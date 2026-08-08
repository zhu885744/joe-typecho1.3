<?php
/* 获取文章列表 已测试 √  */
function _getPost($self)
{
    $self->response->setStatus(200);

    $page = $self->request->page;
    $pageSize = $self->request->pageSize;
    $type = $self->request->type;

    /* sql注入校验 */
    if (!preg_match('/^\d+$/', $page)) {
        return $self->response->throwJson(array("data" => "非法请求！已屏蔽！"));
    }
    if (!preg_match('/^\d+$/', $pageSize)) {
        return $self->response->throwJson(array("data" => "非法请求！已屏蔽！"));
    }
    if (!preg_match('/^(created|views|commentsNum|agree)$/', $type)) {
      return $self->response->throwJson(["data" => "非法请求！已屏蔽！"]);
    }

    /* 如果传入0，强制赋值1 */
    if ((int)$page == 0) $page = 1;

    /* 计算总文章数（用于分页） */
    $db = Typecho_Db::get();
    $totalPosts = $db->fetchObject($db->select(array('COUNT(cid)' => 'total'))
        ->from('table.contents')
        ->where('table.contents.type = ?', 'post')
        ->where('table.contents.status = ?', 'publish')
        ->where('table.contents.created < ?', time()))->total;
    $totalPages = ceil($totalPosts / $pageSize);

    $result = [];
    /* 增加置顶文章功能，通过JS判断（如果你想添加其他标签的话，请先看置顶如何实现的） */
    $sticky_text = Helper::options()->JIndexSticky;
    if ($sticky_text && (int)$page === 1) {
        $sticky_arr = explode("||", $sticky_text);
        foreach ($sticky_arr as $cid) {
            $self->widget('Widget_Contents_Post@' . $cid, 'cid=' . $cid)->to($item);
            if ($item->next()) {
                $result[] = array(
                    "mode" => $item->fields->mode ? $item->fields->mode : 'default',
                    "image" => _getThumbnails($item),
                    "time" => date('Y-m-d', $item->created),
                    "created" => date('Y年m月d日', $item->created),
                    "title" => $item->title,
                    "abstract" => _getAbstract($item, false),
                    "category" => $item->categories,
                    "views" => _getViews($item, false),
                    "commentsNum" => number_format($item->commentsNum),
                    "agree" => _getAgree($item, false),
                    "permalink" => $item->permalink,
                    "lazyload" => _getLazyload(false),
                    "type" => "sticky",
                );
            }
        }
    }
    $self->widget('Widget_Contents_Sort', 'page=' . $page . '&pageSize=' . $pageSize . '&type=' . $type)->to($item);
    while ($item->next()) {
        $result[] = array(
            "mode" => $item->fields->mode ? $item->fields->mode : 'default',
            "image" => _getThumbnails($item),
            "time" => date('Y-m-d', $item->created),
            "created" => date('Y年m月d日', $item->created),
            "title" => $item->title,
            "abstract" => _getAbstract($item, false),
            "category" => $item->categories,
            "views" => number_format($item->views),
            "commentsNum" => number_format($item->commentsNum),
            "agree" => number_format($item->agree),
            "permalink" => $item->permalink,
            "lazyload" => _getLazyload(false),
            "type" => "normal"
        );
    };

    $self->response->throwJson(array(
        "data" => $result,
        "totalPages" => $totalPages,
        "currentPage" => (int)$page,
        "pageSize" => (int)$pageSize,
        "totalPosts" => (int)$totalPosts
    ));
}

/* 增加浏览量 已测试 √ */
function _handleViews($self)
{
    $self->response->setStatus(200);

    $cid = $self->request->cid;

    /* sql注入校验 */
    if (!preg_match('/^\d+$/',  $cid)) {
        return $self->response->throwJson(array("code" => 0, "data" => "非法请求！已屏蔽！"));
    }
    $db = Typecho_Db::get();
    $row = $db->fetchRow($db->select('views')->from('table.contents')->where('cid = ?', $cid));
    if (!empty($row) && isset($row['views'])) {
        $db->query($db->update('table.contents')->rows(array('views' => (int)($row['views'] ?? 0) + 1))->where('cid = ?', $cid));
        $result = $db->fetchRow($db->select('views')->from('table.contents')->where('cid = ?', $cid));
        $self->response->throwJson(array(
            "code" => 1,
            "data" => array('views' => number_format($result['views'] ?? 0))
        ));
    } else {
        $self->response->throwJson(array("code" => 0, "data" => null));
    }
}

/* 点赞和取消点赞 已测试 √ */
function _handleAgree($self)
{
    $self->response->setStatus(200);

    $cid = $self->request->cid;
    $type = $self->request->type;

    /* sql注入校验 */
    if (!preg_match('/^\d+$/',  $cid)) {
        return $self->response->throwJson(array("code" => 0, "data" => "非法请求！已屏蔽！"));
    }
    /* sql注入校验 */
    if (!preg_match('/^(agree|disagree)$/', $type)) {
        return $self->response->throwJson(array("code" => 0, "data" => "非法请求！已屏蔽！"));
    }
    $db = Typecho_Db::get();
    $row = $db->fetchRow($db->select('agree')->from('table.contents')->where('cid = ?', $cid));
    if (!empty($row) && isset($row['agree'])) {
        if ($type === "agree") {
            $db->query($db->update('table.contents')->rows(array('agree' => (int)($row['agree'] ?? 0) + 1))->where('cid = ?', $cid));
        } else {
            $db->query($db->update('table.contents')->rows(array('agree' => max(0, (int)($row['agree'] ?? 0) - 1)))->where('cid = ?', $cid));
        }
        $result = $db->fetchRow($db->select('agree')->from('table.contents')->where('cid = ?', $cid));
        $self->response->throwJson(array(
            "code" => 1,
            "data" => array('agree' => number_format($result['agree'] ?? 0))
        ));
    } else {
        $self->response->throwJson(array("code" => 0, "data" => null));
    }
}

/* 获取壁纸分类 已测试 √ */
function _getWallpaperType($self)
{
    $self->response->setStatus(200);

    $json = _curl("http://cdn.apc.360.cn/index.php?c=WallPaper&a=getAllCategoriesV2&from=360chrome");
    $res = json_decode($json, TRUE);
    if ($res['errno'] == 0) {
        $self->response->throwJson([
            "code" => 1,
            "data" => $res['data']
        ]);
    } else {
        $self->response->throwJson([
            "code" => 0,
            "data" => null
        ]);
    }
}

/* 获取壁纸列表 已测试 √ */
function _getWallpaperList($self)
{
    $self->response->setStatus(200);

    $cid = $self->request->cid;
    $start = $self->request->start;
    $count = $self->request->count;
    $json = _curl("http://wallpaper.apc.360.cn/index.php?c=WallPaper&a=getAppsByCategory&cid={$cid}&start={$start}&count={$count}&from=360chrome");
    $res = json_decode($json, TRUE);
    if ($res['errno'] == 0) {
        $self->response->throwJson([
            "code" => 1,
            "data" => $res['data'],
            "total" => $res['total']
        ]);
    } else {
        $self->response->throwJson([
            "code" => 0,
            "data" => null
        ]);
    }
}

/* 获取虎牙视频列表 已测试 √ */
function _getHuyaList($self)
{
    $self->response->setStatus(200);

    $gameId = $self->request->gameId;
    $page = $self->request->page;
    $json = _curl("https://www.huya.com/cache.php?m=LiveList&do=getLiveListByPage&gameId={$gameId}&tagAll=0&page={$page}");
    $res = json_decode($json, TRUE);
    if ($res['status'] === 200) {
        $self->response->throwJson([
            "code" => 1,
            "data" => $res['data'],
        ]);
    } else {
        $self->response->throwJson([
            "code" => 0,
            "data" => "抓取失败！请联系作者！"
        ]);
    }
}

/* 获取最近评论 */
function _getCommentLately($self)
{
    $self->response->setStatus(200);

    $time = time();
    $num = 7;
    $categories = [];
    $series = [];
    $db = Typecho_Db::get();
    $prefix = $db->getPrefix();
    for ($i = ($num - 1); $i >= 0; $i--) {
        $date = date("Y/m/d", $time - ($i * 24 * 60 * 60));
        $sql = "SELECT coid FROM `{$prefix}comments` WHERE FROM_UNIXTIME(created, '%Y/%m/%d') = '{$date}' limit 100";
        $count = count($db->fetchAll($sql));
        $categories[] = $date;
        $series[] = $count;
    }
    $self->response->throwJson([
        "categories" => $categories,
        "series" => $series,
    ]);
}

/* 获取文章归档 */
function _getArticleFiling($self)
{
    $self->response->setStatus(200);

    $page = $self->request->page;
    $pageSize = 8;
    if (!preg_match('/^\d+$/', $page)) return $self->response->throwJson(array("data" => "非法请求！已屏蔽！"));
    if ((int)$page === 0) $page = 1;
    $offset = $pageSize * ($page - 1);
    $time = time();
    $db = Typecho_Db::get();
    $prefix = $db->getPrefix();
    $result = [];
    
    $sql = "SELECT FROM_UNIXTIME(created, '%Y 年 %m 月') as date 
            FROM `{$prefix}contents` 
            WHERE created < {$time} 
            AND (password is NULL or password = '') 
            AND status = 'publish' 
            AND type = 'post' 
            GROUP BY FROM_UNIXTIME(created, '%Y 年 %m 月')
            ORDER BY date DESC 
            LIMIT {$pageSize} OFFSET {$offset}";
    
    $temp = $db->fetchAll($sql);
    $options = Typecho_Widget::widget('Widget_Options');
    
    foreach ($temp as $item) {
        $date = $item['date'];
        $list = [];
        
        $sql = "SELECT * FROM `{$prefix}contents` 
                WHERE created < {$time} 
                AND (password is NULL or password = '') 
                AND status = 'publish' 
                AND type = 'post' 
                AND FROM_UNIXTIME(created, '%Y 年 %m 月') = '{$date}' 
                ORDER BY created DESC 
                LIMIT 100";
        
        $_list = $db->fetchAll($sql);
        
        foreach ($_list as $_item) {
            $type = $_item['type'];
            $_item['categories'] = $db->fetchAll($db->select()->from('table.metas')
                ->join('table.relationships', 'table.relationships.mid = table.metas.mid')
                ->where('table.relationships.cid = ?', $_item['cid'])
                ->where('table.metas.type = ?', 'category')
                ->order('table.metas.order', Typecho_Db::SORT_ASC));
            
            $_item['category'] = urlencode(current(Typecho_Common::arrayFlatten($_item['categories'], 'slug')));
            $_item['slug'] = urlencode($_item['slug']);
            $_item['date'] = new Typecho_Date($_item['created']);
            $_item['year'] = $_item['date']->year;
            $_item['month'] = $_item['date']->month;
            $_item['day'] = $_item['date']->day;
            
            $routeExists = (NULL != Typecho_Router::get($type));
            $_item['pathinfo'] = $routeExists ? Typecho_Router::url($type, $_item) : '#';
            $_item['permalink'] = Typecho_Common::url($_item['pathinfo'], $options->index);
            
            $list[] = array(
                "title" => date('m/d', $_item['created']) . '：' . $_item['title'],
                "permalink" => $_item['permalink'],
            );
        }
        $result[] = array("date" => $date, "list" => $list);
    }
    $self->response->throwJson($result);
}