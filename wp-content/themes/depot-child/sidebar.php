<aside class="mkd-sidebar">
    <?php
      /***Fix Bug***/
      //Showing sidebar on the product category
      $mkd_sidebar = "sidebar";

      if (is_active_sidebar($mkd_sidebar)) {
        dynamic_sidebar($mkd_sidebar);
      }
    ?>
</aside>