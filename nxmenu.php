<?php
$_ap = $active_page ?? '';
function _nx_link($href, $label, $key, $ap, $base_color = '#aab8d4') {
    $is_active = ($ap === $key);
    $color   = $is_active ? '#fff'                       : $base_color;
    $bg      = 'transparent';
    $border  = $is_active ? 'border-bottom:2px solid #4a90d9;' : 'border-bottom:2px solid transparent;';
    $hover_in  = "this.style.color='#fff';this.style.background='rgba(255,255,255,0.1)'";
    $hover_out = $is_active
        ? "this.style.color='#fff';this.style.background='transparent'"
        : "this.style.color='{$base_color}';this.style.background='transparent'";
    echo "<a href=\"{$href}\" style=\"color:{$color};font-size:12px;text-decoration:none;padding:6px 10px;border-radius:3px;background:{$bg};{$border}box-sizing:border-box;\""
       . " onmouseover=\"{$hover_in}\""
       . " onmouseout=\"{$hover_out}\">{$label}</a>";
}
?>
<nav style="background-color:#001233;color:#fff;padding:0 28px;height:40px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 6px rgba(0,0,0,0.25);">

  <div style="display:flex;align-items:center;gap:4px;">
    <span style="font-size:13px;font-weight:700;letter-spacing:1px;color:#fff;margin-right:20px;">NXTM</span>
    <?php _nx_link('index.php', 'W.Tasks', 'wtasks', $_ap); ?>
    <?php _nx_link('ptask.php', 'P.Tasks', 'ptasks', $_ap); ?>
    <?php _nx_link('ltask.php', 'Links',   'links',  $_ap); ?>
    <?php _nx_link('lists.php', 'Lists',   'lists',  $_ap); ?>
    <?php _nx_link('memos.php', 'Memos',   'memos',  $_ap); ?>
  </div>

  <div style="display:flex;align-items:center;gap:4px;">
    <?php _nx_link('mx.php',     'Admin',  'admin',  $_ap, '#4a6fa5'); ?>
    <?php _nx_link('logout.php', 'Logout', 'logout', $_ap, '#667'); ?>
  </div>

</nav>
