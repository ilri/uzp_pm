<?php require_once 'modules/mod_startup.php'; ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <title>UZP 99H Lab system</title>
      <link rel='stylesheet' type='text/css' href='<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>bootstrap/docs/assets/css/bootstrap.css'/>
      <link rel='stylesheet' type='text/css' href='css/uzp_lab.css'>
      <script type='text/javascript' src='<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js'></script>
      <script type='text/javascript' src='<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery-form/jquery.form.js'></script>
      <script type='text/javascript' src='<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery-json/dist/jquery.json.min.js'></script>
      <script type='text/javascript' src='<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>azizi-shared-libs/common/common_v0.3.js'></script>
      <script type='text/javascript' src='<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>azizi-shared-libs/notification/notification.js'></script>
      <script type='text/javascript' src='<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jsencrypt/bin/jsencrypt.min.js'></script>
      <script type='text/javascript' src='<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>bootstrap/docs/assets/js/bootstrap.min.js' /></script>
      <script type='text/javascript' src='js/uzp_lab.js'></script>
   </head>
   <body>
      <div id='uzp'>
         <div id='uzp_header'>&nbsp;</div>
         <?php $Uzp->TrafficController(); ?>
         <div id='uzp_footer'>UZP - 99H Lab system</div>
      </div>
     <div id='credits'>
        Designed and Developed By: <a href="mailto:a.kihara@cgiar.org" target="_top">Kihara Absolomon</a>, <a href="mailto:j.rogena@cgiar.org" target="_blank">Rogena Jason</a>
     </div>
   </body>
</html>