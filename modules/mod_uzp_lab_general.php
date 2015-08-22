<?php

/**
 * This module will have the general functions that appertains to the system
 *
 * @category   Repository
 * @package    Main
 * @author     Kihara Absolomon <a.kihara@cgiar.org>
 * @since      v0.1
 */
class Uzp extends DBase{

   /**
    * @var Object An object with the database functions and properties. Implemented here to avoid having a million & 1 database connections
    */
   public $Dbase;

   /**
    * @var Object An object that is responsible for all security functions eg (authing user, getting modules user has access to)
    */
   private $security;

   public $addinfo;

   public $footerLinks = '';

   /**
    * @var  string   Just a string to show who is logged in
    */
   public $whoisme = '';

   /**
    * @var  string   A place to store any errors that happens before we have a valid connection
    */
   public $errorPage = '';

   /**
    * @var  bool     A flag to indicate whether we have an error or not
    */
   public $error = false;

   public function  __construct() {
      $this->Dbase = new DBase('mysql');
      $this->Dbase->InitializeConnection();
      if(is_null($this->Dbase->dbcon)) {
         ob_start();
         $this->homePage(OPTIONS_MSSG_DB_CON_ERROR);
         $this->errorPage = ob_get_contents();
         ob_end_clean();
         return;
      }
      $this->Dbase->InitializeLogs();
   }

   public function sessionStart() {
      $this->Dbase->SessionStart();
   }

   /**
    * Controls the program execution
    */
   public function TrafficController(){
      if(OPTIONS_REQUESTED_MODULE != 'login' && !Config::$downloadFile){  //when we are normally browsing, check that we have the right credentials
         //we hope that we have still have the right credentials
         $this->Dbase->ManageSession();
         $this->whoisme = "{$_SESSION['surname']} {$_SESSION['onames']}, {$_SESSION['user_level']}";
      }

      if(!Config::$downloadFile && ($this->Dbase->session['error'] || $this->Dbase->session['timeout'])){
         if(OPTIONS_REQUEST_TYPE == 'normal'){
            $this->LoginPage($this->Dbase->session['message'], $_SESSION['username']);
            return;
         }
         elseif(OPTIONS_REQUEST_TYPE == 'ajax') die('-1' . $this->Dbase->session['message']);
      }

      if(OPTIONS_REQUESTED_MODULE == '') $this->homePage();
      elseif(OPTIONS_REQUESTED_MODULE == 'pm'){
         if(OPTIONS_REQUESTED_SUB_MODULE == '' || OPTIONS_REQUESTED_SUB_MODULE == 'step1') $this->initPmStep1();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step2') $this->initPmStep2();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step3') $this->initPmStep3();
      }
      elseif(OPTIONS_REQUESTED_SUB_MODULE == 'aliq') {
         
      }
      elseif(OPTIONS_REQUESTED_SUB_MODULE == 'backup') {
         $this->dumpData();
      }
   }
   
   /**
    * This function dumps the database corresponding to this project
    * 
    * @return type
    */
   private function dumpData() {
        if(!file_exists(Config::$config['rootdir']."\downloads")) mkdir(Config::$config['rootdir']."\downloads");
		$date = new DateTime();
		$filename = Config::$config['rootdir']."\downloads\uzp_99hh_".$date->format('Y-m-d_H-i-s').'.sql';
		$zipName = $filename.".zip";
		$command = Config::$config['mysqldump']." -u ".Config::$config['user']." -p".Config::$config['pass']." ".Config::$config['dbase'].' > '.$filename;
		shell_exec($command);
		$zip = new ZipArchive();
		$zip->open($zipName, ZipArchive::CREATE);
		$zip->addFile($filename, basename($filename));
		$zip->close();
		header('Content-Description: File Transfer');
		header('Content-Type: application/zip');
		header("Content-Disposition: attachment; filename=".basename($zipName));
		//header('Content-Transfer-Encoding: binary');
		//header('Pragma: public');
		header('Content-Length: '.filesize($zipName));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		ob_clean();
		flush();
		readfile($zipName);
		return;
   }

   /**
    * Creates the home page of the lab system
    * 
    * @param type $error
    */
   private function homePage($addInfo = NULL){
      $addInfo = ($addInfo != '') ? "<div id='addinfo'>$addInfo</div>" : '';
      ?>
<div id='home'>
   <?php echo $addInfo; ?>
   <h3 class="center" id="home_title">UZP - 99H - Lab modules</h3>
   <div class="user_options">
      <ul>
         <li><a href="?page=pm">Postmoterm</a></li>
         <li><a href="?page=aliq">Aliquoting</a></li>
         <li><a href="?page=backup">Backup</a></li>
      </ul>
   </div>
</div>
<script>
   $('#whoisme .back').html('<a href=\'?page=home\'>Back</a>');//back link
</script>
<?php
   }
   
   /**
    * This function attaches the postmoterm template into the DOM
    * 
    */
   private function setPmTemplate($prevUri, $nextUri) {
?>
<link rel="stylesheet" href="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/styles/jqx.base.css" type="text/css" />
<script type="text/javascript" src="js/uzp_lab.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jquery/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxcore.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxinput.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxbuttons.js"></script>
<script type="text/javascript" src="<?php echo OPTIONS_COMMON_FOLDER_PATH; ?>jqwidgets/jqwidgets/jqxnotification.js"></script>
<div id="notification_box"><div id="msg"></div></div>
<?php
      $defaultCss = "background-color: #79B5D2; border: 3px solid #4095BF;";
      $nextCss = $defaultCss;
      $prevCss = $defaultCss;
      $prevHref = "#";
      $nextHref = "#";
      if($nextUri != null) {
         $nextHref = "?page=pm&do=".$nextUri;
         $nextCss = "background-color: #40BF80; border: 3px solid #2D8659;";
      }
      if($prevUri != null) {
         $prevHref = "?page=pm&do=".$prevUri;
         $prevCss = "background-color: #D27F79; border: 3px solid #BF4840;";
      }
      $template = "<div id='pm_tmplt_container'>"
            . "<div id='prev_step' class='circular_div step_toggle_btns' style='$prevCss'><a href='$prevHref'>Prev</a></div>"
            . "<div id='content_container'></div>"
            . "<div id='next_step' class='circular_div step_toggle_btns' style='$nextCss'><a href='$nextHref'>Next</a></div>"
            . "</div>";
      echo $template;
   }
   
   /**
    * This function inits the UZP object correctly using the provided variables
    * 
    * @param String $uri      The current step uri
    * @param String $html     The current step html
    * @param String $prevUri  The previous step uri. Set to null if none
    * @param String $nextUri  The next step uri. Set to null if none
    */
   private function initUZPJs($uri, $html, $prevUri, $nextUri) {
      $this->setPmTemplate($prevUri, $nextUri);
?>
<script type="text/javascript">
   var uzp = new Uzp("<?php echo $uri;?>", "<?php echo $html;?>");
</script>
<?php
      if($prevUri != null) {
?>
<script type="text/javascript">
   var uzp.setPrevStep("<?php echo $prevUri;?>");
</script>
<?php
      }
      if($nextUri != null) {
?>
<script type="text/javascript">
   var uzp.setNextStep("<?php echo $nextUri;?>");
</script>
<?php
      }
   }
   
   private function initPmStep1() {
      $html = "<h3 class='center'>Samples</h3>"
            . "<div class='input_container'>"
            . $this->generateInputPair("Serum", "serum")
            . $this->generateInputPair("1st Blood Smear", "bsmear_1")
            . $this->generateInputPair("2nd Blood Smear", "bsmear_2")
            . $this->generateInputPair("1st Oropharyngeal", "osmear_1")
            . $this->generateInputPair("1st Oropharyngeal", "osmear_2")
            . $this->generateInputPair("Wing biopsy", "wing")
            . $this->generateInputPair("Ectoparasite", "eparasite")
            . "</div>";
      $this->initUZPJs("step1", $html, null, "step2");
   }
   
   private function initPmStep2() {
      $html = "<h3 class='center'>Samples</h3>"
            . "<div class='input_container'>"
            . $this->generateSelectPair("Species", "species", array("spec1" => "Species 1", "spec2" => "Species 2"))
              . $this->generateSelectPair("Species", "species", array("spec1" => "Species 1", "spec2" => "Species 2"))
              . $this->generateSelectPair("ID Certainty", "id_certainty", array("actual" => "Actual", "estimate" => "Estimate", "unknown" => "Unknown"))
              . $this->generateSelectPair("Age Class", "age", array(" neonate" => "Neonate", "juvenile" => "Juvenile", "subadult" => "Subadult", "adult" => "Adult", "unknown" => "Unknown"))
              . $this->generateSelectPair("Sex", "sex", array("male" => "Male", "female" => "Female", "unknown" => "Unknown"))
              . $this->generateSelectPair("Pregnant?", "pregnant", array("yes" => "Yes", "no" => "No"))
              . $this->generateSelectPair("Lactating?", "lactating", array("yes" => "Yes", "no" => "No"))
              . $this->generateSelectPair("Condition at sampling", "cond_samp", array("a_healthy" => "Apparently healthy", "sign_sick" => "Signs of sickness", "injured" => "Injured", "unknown" => "Unknown"))
              . $this->generateInputPair("Describe clinical signs if present", "clcl_sgns")
              . $this->generateSelectPair("Is disease suspected?", 'suspect_dis', array("yes" => "Yes", "no" => "No"))
              . $this->generateInputPair("Suspected disease", "suspctd_dis")
              . $this->generateInputPair("Body condition score", "bcs")
            . "</div>";
      $this->initUZPJs("step2", $html, "step1", "step3");
   }
   
   private function initPmStep3() {
      $html = "<h3 class='center'></h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Body length (mm)", "body_length")
              . $this->generateInputPair("Ear length (mm)", "ear_length")
              . $this->generateInputPair("Tragus length (mm)", "tragus_length")
              . $this->generateInputPair("Forearm length (mm)", "forearm_length")
              . $this->generateInputPair("Tibia length (mm)", "tibia_length")
              . $this->generateInputPair("Hind foot length (mm)", "hfoot_length")
              . $this->generateInputPair("Tail length (mm)", "hfoot_length")
              . $this->generateInputPair("Full body (mm)", "body_length")
              . $this->generateInputPair("Full anterior facial", "body_length")
              . $this->generateInputPair("Full lateral facial/head", "body_length")
              . $this->generateInputPair("Parted pelage on dorsum", "body_length")
              . $this->generateInputPair("Parted pelage on vetrum", "body_length")
              . "</div>";
      $this->initUZPJs("step2", $html, "step2", "step4");
   }
   
   private function generateInputPair($label, $id, $defaultValue = '') {
      $input_id = $id."_input";
      $html = "<div id='$id'><label class='input_label'>$label</label>&nbsp;&nbsp;<input type='text' id='$input_id' name='$input_id' class='input-small' value='$defaultValue' /></div>";
      return $html;
   }
   
   private function generateSelectPair($label, $id, $options, $defaultOption = '') {
      $input_id = $id."_input";
      $html = "<div id='$id'><label class='input_label'>$label</label>&nbsp;&nbsp;<select id='$input_id name='$input_id' value='$defaultOption'>";
      $html .= "<option value=''></option>";
      $optionValues = array_keys($options);
      foreach($optionValues as $currOption) {
         $html .= "<option value='$currOption'>".$options[$currOption]."</option>";
      }
      $html .= "</select></div>";
      return $html;
   }
}
?>