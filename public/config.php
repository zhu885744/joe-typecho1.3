<script>
  localStorage.getItem("data-night") && document.querySelector("html").setAttribute("data-night", "night");
  window.Joe = {
    THEME_URL: `<?php Helper::options()->themeUrl() ?>`,
    BASE_API: `<?php echo $this->options->rewrite == 0 ? Helper::options()->rootUrl . '/index.php/joe/api' : Helper::options()->rootUrl . '/joe/api' ?>`,
    DYNAMIC_BACKGROUND: `<?php $this->options->JDynamic_Background() ?>`,
    WALLPAPER_BACKGROUND_PC: `<?php $this->options->JWallpaper_Background_PC() ?>`,
    IS_MOBILE: /windows phone|iphone|android/gi.test(window.navigator.userAgent),
    BAIDU_PUSH: <?php echo $this->options->JBaiduToken ? 'true' : 'false' ?>,
    DOCUMENT_TITLE: `<?php $this->options->JDocumentTitle() ?>`,
    LAZY_LOAD: `<?php _getLazyload() ?>`,
    BIRTHDAY: `<?php $this->options->JBirthDay() ?>`,
    MOTTO: `<?php _getAsideAuthorMotto() ?>`,
    PAGE_SIZE: `<?php $this->parameter->pageSize() ?>`,
    PAGE_MODE: `<?php echo $this->options->JIndex_Pagination_Mode ?? 'loadmore' ?>`,
    CURRENT_PAGE: `<?php echo (method_exists($this, 'getCurrentPage') && $this->getCurrentPage() > 0) ? (int)$this->getCurrentPage() : 1; ?>`
  }
</script>

<?php
// 获取字体URL并确保是字符串类型
$fontUrl = (string)($this->options->JCustomFont ?? '');
$fontFormat = '';

// 安全地检测字体格式 - 兼容PHP 7.4-8.4
if ($fontUrl !== '') {
    if (strpos($fontUrl, 'woff2') !== false) {
        $fontFormat = 'woff2';
    } elseif (strpos($fontUrl, 'woff') !== false) {
        $fontFormat = 'woff';
    } elseif (strpos($fontUrl, 'ttf') !== false) {
        $fontFormat = 'truetype';
    } elseif (strpos($fontUrl, 'eot') !== false) {
        $fontFormat = 'embedded-opentype';
    } elseif (strpos($fontUrl, 'svg') !== false) {
        $fontFormat = 'svg';
    }
}

// 获取背景图片URL并确保是字符串
$wallpaperWap = (string)($this->options->JWallpaper_Background_WAP ?? '');
$wallpaperPc = (string)($this->options->JWallpaper_Background_PC ?? '');
$backgroundValue = '#f5f5f5';

if (_isMobile()) {
    if ($wallpaperWap !== '') {
        $backgroundValue = "url(" . $wallpaperWap . ")";
    }
} else {
    if ($wallpaperPc !== '') {
        $backgroundValue = "url(" . $wallpaperPc . ")";
    }
}
?>

<style>
  @font-face {
    font-family: 'Joe Font';
    font-weight: 400;
    font-style: normal;
    font-display: swap;
    src: url('<?php echo $fontUrl ?>');
    <?php if ($fontFormat !== '') : ?>
    src: url('<?php echo $fontUrl ?>') format('<?php echo $fontFormat ?>');
    <?php endif; ?>
  }

  body {
    <?php if ($fontUrl !== '') : ?>
    font-family: 'Joe Font', 'Helvetica Neue', Helvetica, 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', '微软雅黑', Arial, sans-serif;
    <?php else : ?>
    font-family: 'Helvetica Neue', Helvetica, 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', '微软雅黑', Arial, sans-serif;
    <?php endif; ?>
  }

  body::before {
    background: <?php echo $backgroundValue; ?>;
    background-position: center 0;
    background-repeat: no-repeat;
    background-size: cover;
  }

  <?php 
  // 安全地输出自定义CSS
  $customCSS = (string)($this->options->JCustomCSS ?? '');
  echo $customCSS;
  ?>
</style>