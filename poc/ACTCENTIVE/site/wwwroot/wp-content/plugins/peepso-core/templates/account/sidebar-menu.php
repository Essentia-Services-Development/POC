    <ul>
      <?php
      foreach ($menus as $menu) {
      ?>
      <li <?php echo $menu['selected']; ?>><a href="<?php echo $menu['url']; ?>"><?php echo $menu['label']; ?></a></li>
      <?php
      }
      ?>
    </ul>