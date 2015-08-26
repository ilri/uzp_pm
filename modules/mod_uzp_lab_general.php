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
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step4') $this->initPmStep4();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step5') $this->initPmStep5();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step6') $this->initPmStep6();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step7') $this->initPmStep7();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step8') $this->initPmStep8();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step9') $this->initPmStep9();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step10') $this->initPmStep10();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step11') $this->initPmStep11();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step12') $this->initPmStep12();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step13') $this->initPmStep13();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step14') $this->initPmStep14();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step15') $this->initPmStep15();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step16') $this->initPmStep16();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step17') $this->initPmStep17();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step18') $this->initPmStep18();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step19') $this->initPmStep19();
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'commit') $this->commitStepData();
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
            . "<div id='prev_step' class='circular_div step_toggle_btns' style='$prevCss'>Prev</div>"
            . "<div id='content_container'></div>"
            . "<div id='next_step' class='circular_div step_toggle_btns' style='$nextCss'>Next</div>"
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
   private function initUZPJs($uri, $html, $lastInputId, $prevUri, $nextUri, $animalId = null) {
      $this->setPmTemplate($prevUri, $nextUri);
?>
<script type="text/javascript">
   var uzp = new Uzp("<?php echo $uri;?>", "<?php echo $html;?>", "<?php echo $lastInputId;?>");
</script>
<?php
      if($prevUri != null) {
?>
<script type="text/javascript">
   window.uzp_lab.setPrevStep("<?php echo $prevUri;?>");
</script>
<?php
      }
      if($nextUri != null) {
?>
<script type="text/javascript">
   window.uzp_lab.setNextStep("<?php echo $nextUri;?>");
</script>
<?php
      }
      if($animalId != null) {
?>
<script type="text/javascript">
   window.uzp_lab.setAnimalId("<?php echo $animalId;?>");
</script>
<?php
      }
   }
   
   private function initPmStep1() {
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Step 1</h3>"
            . "<div class='input_container'>"
            . $this->generateSelectPair("Vet", "vet", array("James Hassell" => "James Hassell", "Allan Ogendo" => "Allan Ogendo", "Yukiko Nakamura" => "Yukiko Nakamura"), $data)
            . $this->generateSelectPair("Assistant", "assistant", array("James Hassell" => "James Hassell", "Allan Ogendo" => "Allan Ogendo", "Yukiko Nakamura" => "Yukiko Nakamura"), $data)
            . $this->generateInputPair("Animal Id", "animal_id", $data)
            . $this->generateSelectPair("Animal Class", "animal_class", array("roden" => "Rodent", "bat" => "Bat"), $data)
            . "</div>";
      $this->initUZPJs("step1", $html, "animal_class_input", null, "step2", $_GET['animal']);
   }
   
   private function initPmStep2() {
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Samples</h3>"
            . "<div class='input_container'>"
            . $this->generateInputPair("Weight", "weight", $data, "number")
            . $this->generateInputPair("EDTA", "edta", $data)
            . $this->generateInputPair("Serum", "serum", $data)
            . $this->generateInputPair("1st Blood Smear", "bsmear_1", $data)
            . $this->generateInputPair("2nd Blood Smear", "bsmear_2", $data)
            . $this->generateInputPair("1st Oropharyngeal", "osmear_1", $data)
            . $this->generateInputPair("1st Oropharyngeal", "osmear_2", $data)
            . $this->generateInputPair("Wing biopsy", "wing", $data)//only if bat
            . $this->generateInputPair("Ectoparasite", "eparasite", $data)
            . "</div>";
      $this->initUZPJs("step2", $html, "eparasite_input", "step1", "step3", $_GET['animal']);
   }
   
   private function initPmStep3() {
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Phenotyping</h3>"
            . "<div class='input_container'>"
              . $this->generateSelectPair("Species", "species", array("spec1" => "Species 1", "spec2" => "Species 2"), $data)
              . $this->generateSelectPair("ID Certainty", "id_certainty", array("actual" => "Actual", "estimate" => "Estimate", "unknown" => "Unknown"), $data)
              . $this->generateSelectPair("Age Class", "age", array(" neonate" => "Neonate", "juvenile" => "Juvenile", "subadult" => "Subadult", "adult" => "Adult", "unknown" => "Unknown"), $data)
              . $this->generateSelectPair("Sex", "sex", array("male" => "Male", "female" => "Female", "unknown" => "Unknown"), $data)
              . $this->generateSelectPair("Pregnant?", "pregnant", array("yes" => "Yes", "no" => "No"), $data)
              . $this->generateSelectPair("Lactating?", "lactating", array("yes" => "Yes", "no" => "No"), $data)
              . $this->generateSelectPair("Condition at sampling", "cond_samp", array("a_healthy" => "Apparently healthy", "sign_sick" => "Signs of sickness", "injured" => "Injured", "unknown" => "Unknown"), $data)
              . $this->generateTextAreaPair("Describe clinical signs if present", "clcl_sgns", $data)
              . $this->generateSelectPair("Is disease suspected?", 'is_dis_suspected', array("yes" => "Yes", "no" => "No"), $data)
              . $this->generateTextAreaPair("Suspected disease", "suspect_dis", $data)
              . $this->generateInputPair("Body condition score", "bcs", $data, "number")
            . "</div>";
      $this->initUZPJs("step3", $html, "bcs_input", "step2", "step4", $_GET['animal']);
   }
   
   private function initPmStep4() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Body Measurements</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Body length (mm)", "body_length", $data, "number")
              . $this->generateInputPair("Ear length (mm)", "ear_length",  $data, "number")
              . $this->generateInputPair("Tragus length (mm)", "tragus_length", $data, "number")//only if bat
              . $this->generateInputPair("Forearm length (mm)", "forearm_length", $data, "number")//only if bat
              . $this->generateInputPair("Tibia length (mm)", "tibia_length", $data, "number")//only if bat
              . $this->generateInputPair("Hind foot length (mm)", "hfoot_length", $data, "number")
              . $this->generateInputPair("Tail length (mm)", "tail_length", $data, "number")
              . $this->generateInputPair("Full body (mm)", "full_body_length", $data, "number")
              . $this->generateInputPair("Full anterior facial", "anterior_facial", $data, "number")
              . $this->generateInputPair("Full lateral facial/head", "lateral_facial", $data, "number")
              . $this->generateInputPair("Parted pelage on dorsum", "pp_dorsum", $data, "number")//only if bat
              . $this->generateInputPair("Parted pelage on vetrum", "pp_vetrum", $data, "number")//only if bat
              . "</div>";
      $this->initUZPJs("step4", $html, "pp_vetrum_input", "step3", "step5", $animalId);
   }
   
   private function initPmStep5() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Step 5</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Integument lesions", "integument_les", $data)
              . $this->generateInputPair("Scan integument", "integument_bc", $data)
              . $this->generateInputPair("Pectoral muscle lesions", "pectoral_les", $data)
              . $this->generateInputPair("Scan pectoral muscle", "pectoral_mc", $data)
              . $this->generateInputPair("Ptagium lesions", "ptagium_les", $data)//only if bat
              . $this->generateInputPair("Scan ptagium", "ptagium_bc", $data)//only if bat
              . "</div>";
      $this->initUZPJs("step5", $html, "ptagium_bc_input", "step4", "step6", $animalId);//if bat then step6 else step7
   }
   
   private function initPmStep6() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Salivary Glands</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Salivary gland lesions", "saliva_les", $data)
              . $this->generateInputPair("Scan salivary glands 1", "saliva_1_bc", $data)
              . $this->generateInputPair("Scan salivary glands 2", "saliva_2_bc", $data)
              . $this->generateInputPair("Scan salivary glands 3", "saliva_3_bc", $data)
              . "</div>";
      $this->initUZPJs("step6", $html, "saliva_3_bc_input", "step5", "step7", $animalId);
   }
   
   private function initPmStep7() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Step 7</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Cavity lesions", "cavity_les", $data)
              . $this->generateInputPair("Diaphgram lesions", "diaphgram_les", $data)
              . $this->generateInputPair("Scan diaphgram", "diaphgram_bc", $data)
              . "</div>";
      $this->initUZPJs("step7", $html, "diaphgram_bc_input", "step6", "step8", $animalId);
   }
   
   private function initPmStep8() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Liver</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Liver lesions", "liver_les", $data)
              . $this->generateInputPair("Liver weight", "liver_weight", $data)
              . $this->generateInputPair("Scan liver 1", "liver_1_bc", $data)
              . $this->generateInputPair("Scan liver 2", "liver_2_bc", $data)
              . $this->generateInputPair("Scan liver 3", "liver_3_bc", $data)
              . "</div>";
      $this->initUZPJs("step8", $html, "liver_3_bc_input", "step7", "step9", $animalId);
   }
   
   private function initPmStep9() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Spleen</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Spleen lesions", "spleen_les", $data)
              . $this->generateInputPair("Spleen weight", "spleen_weight", $data)
              . $this->generateInputPair("Scan spleen 1", "spleen_1_bc", $data)
              . $this->generateInputPair("Scan spleen 2", "spleen_2_bc", $data)
              . $this->generateInputPair("Scan spleen 3", "spleen_3_bc", $data)
              . "</div>";
      $this->initUZPJs("step9", $html, "spleen_3_bc_input", "step8", "step10", $animalId);
   }
   
   private function initPmStep10() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Kidney</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Kidney lesions", "kidney_les", $data)
              . $this->generateInputPair("Kidney weight", "kidney_weight", $data)
              . $this->generateInputPair("Scan kidney 1", "kidney_1_bc", $data)
              . $this->generateInputPair("Scan kidney 2", "kidney_2_bc", $data)
              . $this->generateInputPair("Scan kidney 3", "kidney_3_bc", $data)
              . "</div>";
      $this->initUZPJs("step10", $html, "kidney_3_bc_input", "step9", "step11", $animalId);
   }
   
   private function initPmStep11() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Adrenal</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Adrenal lesions", "adrenal_les", $data)
              . $this->generateInputPair("Adrenal weight", "adrenal_weight", $data)
              . $this->generateInputPair("Scan adrenal", "adrenal_bc", $data)
              . "</div>";
      $this->initUZPJs("step11", $html, "adrenal_bc_input", "step10", "step12", $animalId);
   }
   
   private function initPmStep12() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Heart</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Heart lesions", "heart_les", $data)
              . $this->generateInputPair("Heart weight", "heart_weight", $data)
              . $this->generateInputPair("Scan heart", "heart_bc", $data)
              . "</div>";
      $this->initUZPJs("step12", $html, "heart_bc_input", "step11", "step13", $animalId);
   }
   
   private function initPmStep13() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Lung</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Lung lesions", "lung_les", $data)
              . $this->generateInputPair("Lung weight", "lung_weight", $data)
              . $this->generateInputPair("Scan lung 1", "lung_1_bc", $data)
              . $this->generateInputPair("Scan lung 2", "lung_2_bc", $data)
              . $this->generateInputPair("Scan lung 3", "lung_3_bc", $data)
              . "</div>";
      $this->initUZPJs("step13", $html, "lung_3_bc_input", "step12", "step14", $animalId);
   }
   
   private function initPmStep14() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Step 14</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Pluck lesions", "pluck_les", $data)
              . $this->generateInputPair("Scan pluck", "pluck_bc", $data)
              . $this->generateInputPair("Scan urine", "urine_1_bc", $data)
              . $this->generateInputPair("Scan urine", "urine_2_bc", $data)
              . $this->generateInputPair("Scan femur", "femur_1_bc", $data)
              . $this->generateInputPair("Scan femur", "femur_2_bc", $data)
              . "</div>";
      $this->initUZPJs("step14", $html, "femur_2_bc_input", "step13", "step15", $animalId);
   }
   
   private function initPmStep15() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Brain</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Brain lesions", "brain_les", $data)
              . $this->generateInputPair("Brain weight", "brain_weight", $data)
              . $this->generateInputPair("Scan brain", "brain_bc", $data)
              . "</div>";
      $this->initUZPJs("step15", $html, "brain_bc_input", "step14", "step16", $animalId);
   }
   
   private function initPmStep16() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Faeces</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Scan faeces 1", "faeces_1_bc", $data)
              . $this->generateInputPair("Scan faeces 2", "faeces_2_bc", $data)
              . $this->generateInputPair("Scan faeces 3", "faeces_3_bc", $data)
              . "</div>";
      $this->initUZPJs("step16", $html, "faeces_3_bc_input", "step15", "step17", $animalId);
   }
   
   private function initPmStep17() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Urogenital</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Urogenital lesions", "urogen_les", $data)
              . $this->generateInputPair("Scan urogenital", "urogen_1_bc", $data)
              . $this->generateInputPair("Scan urogenital", "urogen_2_bc", $data)
              . "</div>";
      $this->initUZPJs("step17", $html, "urogen_2_bc_input", "step16", "step18", $animalId);
   }
   
   private function initPmStep18() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Stomach & Ileum</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Stomach lesions", "stomach_les", $data)
              . $this->generateInputPair("Scan stomach", "stomach_bc", $data)
              . $this->generateInputPair("Ileum lesions", "ileum_les", $data)
              . $this->generateInputPair("Scan ileum 1", "ileum_1_bc", $data)
              . $this->generateInputPair("Scan ileum 2", "ileum_2_bc", $data)
              . "</div>";
      $this->initUZPJs("step18", $html, "ileum_2_bc_input", "step17", "step19", $animalId);
   }
   
   private function initPmStep19() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Commit</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Carcas barcode", "carcas_bc", $data)
              . $this->generateTextAreaPair("General Comment", "general_comment", $data)
              . "</div>";
      $this->initUZPJs("step19", $html, "carcas_bc_input", "step18", null, $animalId);
   }
   
   private function generateInputPair($label, $id, $data = null, $type = 'text') {
      $defaultValue = '';
      if($data != null && isset($data[$id])) $defaultValue = $data[$id];
      $input_id = $id."_input";
      $html = "<div id='$id'><label class='input_label'>$label</label>&nbsp;&nbsp;<input type='$type' id='$input_id' name='$input_id' class='input-small' value='$defaultValue' /></div>";
      return $html;
   }
   
   private function generateSelectPair($label, $id, $options, $data = null) {
      $defaultValue = '';
      if($data != null && isset($data[$id])) $defaultValue = $data[$id];
      $input_id = $id."_input";
      $html = "<div id='$id'><label class='input_label'>$label</label>&nbsp;&nbsp;<select id='$input_id name='$input_id'>";
      $html .= "<option value=''></option>";
      $optionValues = array_keys($options);
      foreach($optionValues as $currOption) {
         $selected = '';
         if($defaultValue == $currOption) $selected = "selected";
         $html .= "<option value='$currOption' $selected>".$options[$currOption]."</option>";
      }
      $html .= "</select></div>";
      return $html;
   }
   
   private function generateTextAreaPair($label, $id, $data = null) {
      $defaultValue = '';
      if($data != null && isset($data[$id])) $defaultValue = $data[$id];
      $input_id = $id."_input";
      $html = "<div id='$id'><label class='input_label'>$label</label>&nbsp;&nbsp;<textarea cols='4' rows='2' id='$input_id' name='$input_id'>$defaultValue</textarea></div>";
      return $html;
   }
   
   private function commitStepData() {
      $response = array();
      $currStep = $_GET['curr_step'];
      $animalId = $_GET['animal'];
      $now = new DateTime('now');
      $postFields = array_keys($_POST);
      $postData = array();
      foreach($postFields as $currField) {
         $postData[$currField] = $_POST[$currField];
      }
      $nowTime = $now->format('Y-m-d H:i:s');
      $postFields[] = "end_time";
      $postData['end_time'] = $nowTime;
      if((strlen($animalId) == 0 || $animalId == null || strtolower($animalId) == 'null') && $currStep == "step1") {//the first step. We really dont expect to have an animal id at this point
         $postFields[] = "start_time";
         $postData['start_time'] = $nowTime;
         $query = "insert into postmortem(";
         for($index = 0; $index < count($postFields); $index++) {
            if($index < (count($postFields) - 1))  $query .= $postFields[$index].", ";
            else $query .= $postFields[$index].")";
         }
         $query .= " values(";
         for($index = 0; $index < count($postFields); $index++) {
            if($index < (count($postFields) - 1))  $query .= ":".$postFields[$index].", ";
            else $query .= ":".$postFields[$index].")";
         }
         $this->Dbase->ExecuteQuery($query, $postData);
         $query = "select id from postmortem where start_time = :time and end_time = :time";
         $result = $this->Dbase->ExecuteQuery($query, array("time" => $nowTime));
         if(is_array($result) && count($result) == 1) {
            $response['error'] = false;
            $response['message'] = 'Animal added to database';
            $response['animal'] = $result[0]['id'];
         }
         else {
            $response['error'] = true;
            $response['message'] = "Unable to add the animal to the database";
         }
      }
      else if(strlen($animalId) > 0) {//the animal id should be set for all other steps
         $query = "update postmortem set ";
         for($index = 0; $index < count($postFields); $index++) {
            if($index < (count($postFields) - 1))  $query .= $postFields[$index]." = :".$postFields[$index].", ";
            else $query .= $postFields[$index]." = :".$postFields[$index]." where ";
         }
         $query .= "id = :animal";
         $postData['animal'] = $animalId;
         $this->Dbase->ExecuteQuery($query, $postData);
         $response['error'] = false;
         $response['message'] = "Previous step committed";
         $response['animal'] = $animalId;
      }
      else {
         $response['error'] = true;
         $response['message'] = "The animal ID is not set. Start postmoterm from the beginning";
      }
      die(json_encode($response));
   }
   
   private function getAnimalData($id) {
      $query = "select * from postmortem where id = :id";
      $result = $this->Dbase->ExecuteQuery($query, array("id" => $id));
      if(is_array($result) && count($result) == 1) return $result[0];
      return null;
   }
}
?>