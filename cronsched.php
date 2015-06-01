<?php
  function getCronJobs() {
    $magePath = "./app/Mage.php";
    $modelPath = "./app/code/core/Mage/Cron/Model/Observer.php";

    if (! (file_exists($magePath) && file_exists($modelPath)) ) {
      die("This script should be ran from within the Magento root directory\n");
    }

    require($magePath);
    require($modelPath);

    Mage::init();

    $cron = new Mage_Cron_Model_Observer();

    $config = Mage::getConfig()->getNode('crontab/jobs')->asArray();
    foreach ($config as $key => $job) {
      printf("%-15s%-30s<br>", "Job:", $key);
      foreach ($job as $schjob) {
        if (array_key_exists("cron_expr", $schjob)) {
          printf("%-15s%-30s<br>", "Schedule:", $schjob['cron_expr']);
        }
        if (array_key_exists("model", $schjob)) {
          printf("%-15s%-30s<br>", "Model:", $schjob['model']);
        }
      }
      print("<br>");
    }

    $config = Mage::getConfig()->getNode('default/crontab/jobs')->asArray();
    foreach ($config as $key => $job) {
      printf("%-15s%-30s<br>", "Job:", $key);
      foreach ($job as $schjob) {
        if (array_key_exists("cron_expr", $schjob)) {
          printf("%-15s%-30s<br>", "Schedule:", $schjob['cron_expr']);
        }
        if (array_key_exists("model", $schjob)) {
          printf("%-15s%-30s<br>", "Model:", $schjob['model']);
        }
      }
      print("<br>");
    }
  }

getCronJobs();

?>
