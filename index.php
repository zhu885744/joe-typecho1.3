<?php

/**
 * 一款基于 Typecho 博客的双栏极致优化主题<br>环境要求：<br>typecho 1.2-1.3<br>PHP 7.4 ~ 8.5
 * @package joe
 * @author 不语
 * @version 7.7.5
 * @link https://zhuxu.asia/
 */

// 兼容 PHP 8.x 的错误处理（抑制废弃警告）
error_reporting(E_ALL & ~E_DEPRECATED);

?>
<!DOCTYPE html>
<html lang="zh-CN">

<head>
  <?php $this->need('public/include.php'); ?>
  <link href="<?php echo _getAssets('assets/lib/swiper@5.4.5/swiper.min.css'); ?>" rel="stylesheet" />
  <script src="<?php echo _getAssets('assets/lib/swiper@5.4.5/swiper.min.js'); ?>"></script>
  <script src="<?php echo _getAssets('assets/lib/wowjs@1.1.3/wow.min.js'); ?>"></script>
  <link href="<?php echo _getAssets('assets/css/joe.index.min.css'); ?>" rel="stylesheet">
  <script src="<?php echo _getAssets('assets/js/joe.index.min.js'); ?>"></script>
</head>

<body>
  <div id="Joe">
    <?php $this->need('public/header.php'); ?>
    <div class="joe_container">
      <div class="joe_main">
        <div class="joe_index">
          <?php
          $carousel = [];
          $carousel_text = $this->options->JIndex_Carousel ?? ''; // PHP 7.4+ 空合并运算符
          if (!empty($carousel_text)) {
            // 兼容不同换行符（PHP 8.x 对换行符处理更严格）
            $carousel_arr = preg_split('/\r\n|\r|\n/', trim($carousel_text));
            if (count($carousel_arr) > 0) {
              foreach ($carousel_arr as $carousel_item) { // 替换 for 循环为 foreach，更安全
                $carousel_parts = explode("||", $carousel_item);
                // 严格检查数组长度，避免 PHP 8.x 未定义索引警告
                if (count($carousel_parts) >= 3) {
                  $img = trim($carousel_parts[0] ?? '');
                  $url = trim($carousel_parts[1] ?? '');
                  $title = trim($carousel_parts[2] ?? '');
                  $carousel[] = [
                    "img" => $img,
                    "url" => $url,
                    "title" => $title
                  ];
                }
              }
            }
          }
          
          $recommend = [];
          $recommend_text = $this->options->JIndex_Recommend ?? '';
          if (!empty($recommend_text)) {
            $recommend_arr = explode("||", $recommend_text);
            if (count($recommend_arr) === 2) {
              $recommend = array_map('trim', $recommend_arr); // 统一清理空格
            }
          }
          ?>
          
          <?php if (!empty($carousel) || count($recommend) === 2) : ?>
            <div class="joe_index__banner">
              <?php if (!empty($carousel)) : ?>
                <div class="swiper-container">
                  <div class="swiper-wrapper">
                    <?php foreach ($carousel as $item) : 
                      // 空值保护，避免 PHP 8.x 警告
                      $item_url = $item['url'] ?? '#';
                      $item_img = $item['img'] ?? '';
                      $item_title = htmlspecialchars($item['title'] ?? '', ENT_QUOTES); // 防 XSS + 兼容
                    ?>
                      <div class="swiper-slide">
                        <a class="item" href="<?php echo $item_url; ?>" target="_blank" rel="noopener noreferrer nofollow">
                          <img width="100%" height="100%" class="thumbnail lazyload" 
                               src="<?php echo _getLazyload(); ?>" 
                               data-src="<?php echo $item_img; ?>" 
                               alt="<?php echo $item_title; ?>" />
                          <div class="title"><?php echo $item_title; ?></div>
                          <svg class="icon" viewBox="0 0 1026 1024" xmlns="http://www.w3.org/2000/svg" width="18" height="18">
                            <path d="M784.3 1007.961a33.2 33.2 0 0 1-27.106-9.062L540.669 854.55 431.766 962.813c-9.062 9.062-36.168 18.044-45.23 9.062a49.72 49.72 0 0 1-27.106-45.23V727.763a33.2 33.2 0 0 1 9.463-27.106l343.071-370.578a44.748 44.748 0 0 1 63.274 63.274l-334.17 361.515v72.175l63.273-54.211a42.583 42.583 0 0 1 54.212-9.062l198.64 126.386L910.847 140.34 151.647 510.837 323.343 619.34c18.044 9.062 27.106 45.23 9.062 63.273-9.062 18.044-45.23 27.106-63.273 18.044L34.082 547.005c-8.981-8.982-18.043-17.723-18.043-36.168s9.062-27.105 27.105-36.167l903.79-451.815c18.043-9.062 36.167-9.062 45.229 0 18.284 9.223 18.284 27.106 18.284 45.15L829.69 971.794c0 18.043-9.062 27.105-27.105 36.167z" />
                          </svg>
                        </a>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <div class="swiper-pagination"></div>
                  <div class="swiper-button-next"></div>
                  <div class="swiper-button-prev"></div>
                </div>
              <?php endif; ?>
              
              <?php if (count($recommend) === 2) : ?>
                <div class="joe_index__banner-recommend <?php echo empty($carousel) ? 'noswiper' : '' ?>">
                  <?php foreach ($recommend as $cid) : 
                    $cid = intval($cid); // 强制转为整数，兼容 PHP 8.x 类型严格性
                    if ($cid <= 0) continue; // 跳过无效 ID
                  ?>
                    <?php $this->widget('Widget_Contents_Post@' . $cid, 'cid=' . $cid)->to($item); ?>
                    <figure class="item">
                      <a class="thumbnail" href="<?php $item->permalink() ?>" title="<?php echo htmlspecialchars($item->title(), ENT_QUOTES); ?>">
                        <img width="100%" height="100%" class="lazyload" 
                             src="<?php echo _getLazyload(); ?>" 
                             data-src="<?php echo isset(_getThumbnails($item)[0]) ? _getThumbnails($item)[0] : ''; ?>" 
                             alt="<?php echo htmlspecialchars($item->title(), ENT_QUOTES); ?>" />
                      </a>
                      <figcaption class="information">
                        <span class="type">推荐</span>
                        <div class="text"><?php $item->title() ?></div>
                      </figcaption>
                    </figure>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          
          <?php if ($this->options->JIndex_Hot === "on") : ?>
            <?php $this->widget('Widget_Contents_Hot@Index', 'pageSize=4')->to($item); ?>
            <div class="joe_index__hot">
              <ul class="joe_index__hot-list">
                <?php while ($item->next()) : ?>
                  <li class="item">
                    <a class="link" href="<?php $item->permalink(); ?>" title="<?php echo htmlspecialchars($item->title(), ENT_QUOTES); ?>">
                      <figure class="inner">
                        <span class="views"><?php echo number_format($item->views ?? 0); ?> ℃</span>
                        <img width="100%" height="120" class="image lazyload" 
                             src="<?php echo _getLazyload(); ?>" 
                             data-src="<?php echo isset(_getThumbnails($item)[0]) ? _getThumbnails($item)[0] : ''; ?>" 
                             alt="<?php echo htmlspecialchars($item->title(), ENT_QUOTES); ?>" />
                        <figcaption class="title"><?php $item->title(); ?></figcaption>
                      </figure>
                    </a>
                  </li>
                <?php endwhile; ?>
              </ul>
            </div>
          <?php endif; ?>
          
          <?php
          $index_ad_text = $this->options->JIndex_Ad ?? '';
          $index_ad = null;
          if (!empty($index_ad_text)) {
            $index_ad_arr = explode("||", $index_ad_text);
            if (count($index_ad_arr) === 2) {
              $index_ad = [
                "image" => trim($index_ad_arr[0] ?? ''),
                "url" => trim($index_ad_arr[1] ?? '')
              ];
            }
          }
          ?>
          
          <?php if (!empty($index_ad) && !empty($index_ad['image']) && !empty($index_ad['url'])) : ?>
            <div class="joe_index__ad">
              <a class="joe_index__ad-link" href="<?php echo $index_ad['url']; ?>" target="_blank" rel="noopener noreferrer nofollow">
                <img width="100%" height="200" class="image lazyload" 
                     src="<?php echo _getLazyload(); ?>" 
                     data-src="<?php echo $index_ad['image']; ?>" 
                     alt="<?php echo htmlspecialchars($index_ad['url'], ENT_QUOTES); ?>" />
                <span class="icon">广告</span>
              </a>
            </div>
          <?php endif; ?>

          <div class="joe_index__title">
            <ul class="joe_index__title-title">
              <li class="item" data-type="created">最新文章</li>
              <li class="item" data-type="views">热门文章</li>
              <li class="item" data-type="commentsNum">评论最多</li>
              <li class="item" data-type="agree">点赞最多</li>
              <li class="line"></li>
            </ul>
            
            <?php
            $index_notice_text = $this->options->JIndex_Notice ?? '';
            $index_notice = null;
            if (!empty($index_notice_text)) {
              $index_notice_arr = explode("||", $index_notice_text);
              if (count($index_notice_arr) === 2) {
                $index_notice = [
                  "text" => trim($index_notice_arr[0] ?? ''),
                  "url" => trim($index_notice_arr[1] ?? '')
                ];
              }
            }
            ?>
            
            <?php if (!empty($index_notice) && !empty($index_notice['text']) && !empty($index_notice['url'])) : ?>
              <div class="joe_index__title-notice">
                <svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" width="20" height="20">
                  <path d="M656.261 347.208a188.652 188.652 0 1 0 0 324.05v-324.05z" fill="#F4CA1C" />
                  <path d="M668.35 118.881a73.35 73.35 0 0 0-71.169-4.06l-310.01 148.68a4.608 4.608 0 0 1-2.013.46h-155.11a73.728 73.728 0 0 0-73.728 73.636v349.64a73.728 73.728 0 0 0 73.728 73.636h156.554a4.68 4.68 0 0 1 1.94.43l309.592 143.196a73.702 73.702 0 0 0 104.668-66.82V181.206a73.216 73.216 0 0 0-34.453-62.326zM125.403 687.237v-349.64a4.608 4.608 0 0 1 4.608-4.608h122.035v358.882H130.048a4.608 4.608 0 0 1-4.644-4.634zm508.319 150.441a4.608 4.608 0 0 1-6.564 4.193L321.132 700.32V323.773l305.97-146.723a4.608 4.608 0 0 1 6.62 4.157v656.471zM938.26 478.72H788.01a34.509 34.509 0 1 0 0 69.018H938.26a34.509 34.509 0 1 0 0-69.018zM810.01 360.96a34.447 34.447 0 0 0 24.417-10.102l106.245-106.122a34.524 34.524 0 0 0-48.84-48.809L785.587 302.08a34.509 34.509 0 0 0 24.423 58.88zm24.417 314.609a34.524 34.524 0 1 0-48.84 48.814L891.832 830.52a34.524 34.524 0 0 0 48.84-48.809z" fill="#595BB3" />
                </svg>
                <a href="<?php echo $index_notice['url']; ?>" target="_blank" rel="noopener noreferrer nofollow">
                  <?php echo htmlspecialchars($index_notice['text'], ENT_QUOTES); ?>
                </a>
              </div>
            <?php endif; ?>
          </div>
          
          <?php 
          $pageMode = $this->options->JIndex_Pagination_Mode ?? 'loadmore';
          $showLoadMore = ($pageMode === 'loadmore' || $pageMode === 'both');
          $showNumeric = ($pageMode === 'numeric' || $pageMode === 'both');
          $isPage1 = ($this->getCurrentPage() <= 1);
          
          /* 输出单篇文章的函数（与 joe.index.min.js 中 getListMode 结构100%一致） */
          function _renderIndexItem($post, $isSticky = false) {
            $postMode = isset($post->fields->mode) ? $post->fields->mode : '';
            $modeClass = ($postMode === 'default' || $postMode === '' || $postMode === 'single' || $postMode === 'multiple' || $postMode === 'none') 
                ? $postMode : 'default';
            if ($modeClass === '') $modeClass = 'default';
            $thumbnails = _getThumbnails($post);
            $thumbnail0 = is_array($thumbnails) && isset($thumbnails[0]) ? $thumbnails[0] : '';
            $time = date('Y-m-d', $post->created);
            $created = date('Y年m月d日', $post->created);
            $title = $post->title;
            $abstract = _getAbstract($post, false);
            $categories = $post->categories;
            $hasCategory = is_array($categories) && count($categories) > 0;
            $views = _getViews($post, false);
            $commentsNum = number_format($post->commentsNum);
            $agree = _getAgree($post, false);
            $permalink = $post->permalink;
            $lazyload = _getLazyload(false);
            $badgeDisplay = $isSticky ? 'inline-block' : 'none';
            $catDisplay = $hasCategory ? 'flex' : 'none';
            $catName = $hasCategory ? $categories[0]->name : '';
            $catLink = $hasCategory ? $categories[0]->permalink : '#';
            
            // SVG 图标（和原 JS 保持一致）
            $imageSvg = '<svg viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" width="20" height="20"><path d="M903.93 107.306H115.787c-51.213 0-93.204 42.505-93.204 93.72V825.29c0 51.724 41.99 93.717 93.717 93.717h788.144c51.72 0 93.717-41.993 93.717-93.717V201.025c-.512-51.214-43.017-93.719-94.23-93.719zm-788.144 64.527h788.657c16.385 0 29.704 13.316 29.704 29.704v390.229L760.54 402.285c-12.805-13.828-30.217-21.508-48.14-19.971-17.924 1.02-34.821 10.754-46.602 26.114l-172.582 239.16-87.06-85.52c-12.29-11.783-27.654-17.924-44.039-17.924-16.39.508-31.755 7.676-43.53 20.48L86.595 821.705V202.05c-1.025-17.416 12.804-30.73 29.191-30.217zm788.145 683.674H141.906l222.255-245.82 87.06 86.037c12.8 12.807 30.212 18.95 47.115 17.417 17.41-1.538 33.797-11.266 45.063-26.118l172.584-238.647 216.111 236.088 2.051-1.54V825.8c.509 16.39-13.315 29.706-30.214 29.706zm0 0"/><path d="M318.072 509.827c79.89 0 144.417-65.037 144.417-144.416 0-79.378-64.527-144.925-144.417-144.925-79.891 0-144.416 64.527-144.416 144.412 0 79.892 64.525 144.93 144.416 144.93zm0-225.327c44.553 0 80.912 36.362 80.912 80.91 0 44.557-35.847 81.43-80.912 81.43-45.068 0-80.916-36.36-80.916-80.915 0-44.556 36.872-81.425 80.916-81.425zm0 0"/></svg>';
            $categorySvg = '<svg class="icon" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" width="15" height="15"><path d="M512.2 564.743a76.818 76.818 0 0 1-30.973-6.508L108.224 393.877c-26.105-11.508-42.56-35.755-42.927-63.272-.384-27.44 15.356-52.053 41.042-64.232l373.004-176.74c20.591-9.737 45.16-9.755 65.75.017L917.68 266.39c25.668 12.188 41.39 36.792 41.024 64.231-.384 27.5-16.821 51.73-42.908 63.237l-372.57 164.377a77.18 77.18 0 0 1-31.025 6.508zM139.843 329.592l370.213 163.241c1.291.56 3.018.567 4.345-.009l369.758-163.128-369.706-175.464v-.01c-1.326-.628-3.158-.636-4.502 0l-370.108 175.37zm748.015 1.858h.175-.175zM512.376 941.674c-10.348 0-20.8-2.32-30.537-6.997L121.05 778.624c-18.113-7.834-26.454-28.87-18.62-46.983 7.835-18.112 28.862-26.488 46.993-18.61l362.08 156.629 345.26-156.366c17.939-8.166 39.14-.253 47.324 17.746 8.166 17.964.227 39.157-17.729 47.324l-344.51 156.61c-9.196 4.449-19.281 6.7-29.471 6.7z" fill="#444"/><path d="M871.563 515.449L511.81 671.775 152.358 515.787v73.578a34.248 34.248 0 0 0 20.76 31.48l301.518 129.19c11.806 5.703 24.499 8.546 37.175 8.546s25.367-2.843 37.174-8.546L850.82 620.534a34.248 34.248 0 0 0 20.744-31.474V515.45z" fill="#ff6a18"/></svg>';
            
            $metaHtml = <<<HTML
              <div class="meta">
                <ul class="items">
                  <li>{$created}</li>
                  <li>{$views} 阅读</li>
                  <li>{$commentsNum} 评论</li>
                  <li>{$agree} 点赞</li>
                </ul>
                <div class="last" style="display: {$catDisplay}">
                  {$categorySvg}
                  <a class="link" target="_self" rel="noopener noreferrer" href="{$catLink}">{$catName}</a>
                </div>
              </div>
HTML;
            
            if ($modeClass === 'default') {
              return <<<HTML
              <li class="joe_list__item wow default">
                <div class="line"></div>
                <a href="{$permalink}" class="thumbnail" title="{$title}" target="_self" rel="noopener noreferrer">
                  <img width="100%" height="100%" class="lazyload" src="{$lazyload}" data-src="{$thumbnail0}" alt="{$title}" />
                  <time datetime="{$time}">{$time}</time>
                  {$imageSvg}
                </a>
                <div class="information">
                  <a href="{$permalink}" class="title" title="{$title}" target="_self" rel="noopener noreferrer">
                    <span class="badge" style="display: {$badgeDisplay}">置顶</span>{$title}
                  </a>
                  <a class="abstract" href="{$permalink}" title="文章摘要" target="_self" rel="noopener noreferrer">{$abstract}</a>
                  {$metaHtml}
                </div>
              </li>
HTML;
            } elseif ($modeClass === 'single') {
              return <<<HTML
              <li class="joe_list__item wow single">
                <div class="line"></div>
                <div class="information">
                  <a href="{$permalink}" class="title" title="{$title}" target="_self" rel="noopener noreferrer">
                    <span class="badge" style="display: {$badgeDisplay}">置顶</span>{$title}
                  </a>
                  {$metaHtml}
                </div>
                <a href="{$permalink}" class="thumbnail" title="{$title}" target="_self" rel="noopener noreferrer">
                  <img width="100%" height="100%" class="lazyload" src="{$lazyload}" data-src="{$thumbnail0}" alt="{$title}" />
                  <time datetime="{$time}">{$time}</time>
                  {$imageSvg}
                </a>
                <div class="information" style="margin-bottom: 0;">
                  <a class="abstract" href="{$permalink}" title="文章摘要" target="_self" rel="noopener noreferrer">{$abstract}</a>
                </div>
              </li>
HTML;
            } elseif ($modeClass === 'multiple') {
              $imgsHtml = '';
              for ($xi = 0; $xi < 3; $xi++) {
                if (isset($thumbnails[$xi])) {
                  $imgsHtml .= '<img width="100%" height="100%" class="lazyload" src="' . $lazyload . '" data-src="' . $thumbnails[$xi] . '" alt="' . $title . '" />';
                }
              }
              return <<<HTML
              <li class="joe_list__item wow multiple">
                <div class="line"></div>
                <div class="information">
                  <a href="{$permalink}" class="title" title="{$title}" target="_self" rel="noopener noreferrer">
                    <span class="badge" style="display: {$badgeDisplay}">置顶</span>{$title}
                  </a>
                  <a class="abstract" href="{$permalink}" title="文章摘要" target="_self" rel="noopener noreferrer">{$abstract}</a>
                </div>
                <a href="{$permalink}" class="thumbnail" title="{$title}" target="_self" rel="noopener noreferrer">
                  {$imgsHtml}
                </a>
                {$metaHtml}
              </li>
HTML;
            } else {
              return <<<HTML
              <li class="joe_list__item wow none">
                <div class="line"></div>
                <div class="information">
                  <a href="{$permalink}" class="title" title="{$title}" target="_self" rel="noopener noreferrer">
                    <span class="badge" style="display: {$badgeDisplay}">置顶</span>{$title}
                  </a>
                  <a class="abstract" href="{$permalink}" title="文章摘要" target="_self" rel="noopener noreferrer">{$abstract}</a>
                  {$metaHtml}
                </div>
              </li>
HTML;
            }
          }
          ?>
          <div class="joe_index__list" data-wow="<?php echo $this->options->JList_Animate() ?? ''; ?>">
            <ul class="joe_list">
              <?php 
              /* 第1页输出置顶文章 */
              if ($isPage1) {
                $sticky_text = $this->options->JIndexSticky ?? '';
                if (!empty($sticky_text)) {
                  $sticky_arr = explode("||", $sticky_text);
                  foreach ($sticky_arr as $sticky_cid) {
                    $sticky_cid = intval(trim($sticky_cid));
                    if ($sticky_cid <= 0) continue;
                    $this->widget('Widget_Contents_Post@' . $sticky_cid, 'cid=' . $sticky_cid)->to($stickyItem);
                    if ($stickyItem->next()) {
                      echo _renderIndexItem($stickyItem, true);
                    }
                  }
                }
              }
              /* 常规文章循环 */
              if ($this->have()) : 
                while ($this->next()) :
                  echo _renderIndexItem($this, false);
                endwhile;
              endif;
              ?>
            </ul>
            <ul class="joe_list__loading">
              <li class="item">
                <div class="thumbnail"></div>
                <div class="information">
                  <div class="title"></div>
                  <div class="abstract">
                    <p></p>
                    <p></p>
                  </div>
                </div>
              </li>
              <li class="item">
                <div class="thumbnail"></div>
                <div class="information">
                  <div class="title"></div>
                  <div class="abstract">
                    <p></p>
                    <p></p>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
        <?php if ($showLoadMore) : ?>
          <div class="joe_load">查看更多</div>
        <?php endif; ?>
        <?php if ($showNumeric) : ?>
          <?php 
          $this->pageNav(
            '<svg class="icon icon-prev" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" width="12" height="12"><path d="M822.272 146.944l-396.8 396.8c-19.456 19.456-51.2 19.456-70.656 0-18.944-19.456-18.944-51.2 0-70.656l396.8-396.8c19.456-19.456 51.2-19.456 70.656 0 18.944 19.456 18.944 45.056 0 70.656z"/><path d="M745.472 940.544l-396.8-396.8c-19.456-19.456-19.456-51.2 0-70.656 19.456-19.456 51.2-19.456 70.656 0l403.456 390.144c19.456 25.6 19.456 51.2 0 76.8-26.112 19.968-51.712 19.968-77.312.512zm-564.224-63.488c0-3.584 0-7.68.512-11.264h-.512v-714.24h.512c-.512-3.584-.512-7.168-.512-11.264 0-43.008 21.504-78.336 48.128-78.336s48.128 34.816 48.128 78.336c0 3.584 0 7.68-.512 11.264h.512v714.24h-.512c.512 3.584.512 7.168.512 11.264 0 43.008-21.504 78.336-48.128 78.336s-48.128-35.328-48.128-78.336z"/></svg>',
            '<svg class="icon icon-next" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg" width="12" height="12"><path d="M822.272 146.944l-396.8 396.8c-19.456 19.456-51.2 19.456-70.656 0-18.944-19.456-18.944-51.2 0-70.656l396.8-396.8c19.456-19.456 51.2-19.456 70.656 0 18.944 19.456 18.944 45.056 0 70.656z"/><path d="M745.472 940.544l-396.8-396.8c-19.456-19.456-19.456-51.2 0-70.656 19.456-19.456 51.2-19.456 70.656 0l403.456 390.144c19.456 25.6 19.456 51.2 0 76.8-26.112 19.968-51.712 19.968-77.312.512zm-564.224-63.488c0-3.584 0-7.68.512-11.264h-.512v-714.24h.512c-.512-3.584-.512-7.168-.512-11.264 0-43.008 21.504-78.336 48.128-78.336s48.128 34.816 48.128 78.336c0 3.584 0 7.68-.512 11.264h.512v714.24h-.512c.512 3.584.512 7.168.512 11.264 0 43.008-21.504 78.336-48.128 78.336s-48.128-35.328-48.128-78.336z"/></svg>',
            1,
            '...',
            array(
              'wrapTag' => 'ul',
              'wrapClass' => 'joe_pagination',
              'itemTag' => 'li',
              'textTag' => 'a',
              'currentClass' => 'active',
              'prevClass' => 'prev',
              'nextClass' => 'next'
            )
          );
          ?>
        <?php endif; ?>
      </div>
      <?php $this->need('public/aside.php'); ?>
    </div>
    <?php $this->need('public/footer.php'); ?>
  </div>
</body>

</html>