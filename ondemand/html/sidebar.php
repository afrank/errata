<? if(!empty($sidebar_nav)) { ?>
        <div class="span3">
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <? foreach($sidebar_nav as $heading=>$navs) { ?>
              <li class="nav-header"><?=$heading?></li>
              <? foreach($navs as $nav) { ?>
              <? if($nav['link'] == $page) { ?>
              <li class="active">
              <? } else { ?>
              <li>
              <? } ?>
              <a href="<?=$nav['link']?>">
              <? if($nav['icon']) { ?>
                <i class="icon-<?=$nav['icon']?>"></i>
              <? } ?>
                <?=$nav['title']?>
              </a>
              <? if($nav['subnav']) { ?>
              <ul class="nav nav-list sub-nav">
                 <? foreach($nav['subnav'] as $subnav) { ?>
                 <? if($subnav['link'] == $page) { ?>
                 <li class="active">
                 <? } else { ?>
                 <li>
                 <? } ?>
                 <a href="<?=$subnav['link']?>"><?=$subnav['title']?></a></li>
                 <? } ?>
              </ul>
              <? } ?>
              </li>
              <? } } ?>
            </ul>
          </div><!--/.well -->
        </div><!--/span-->
<? } ?>
