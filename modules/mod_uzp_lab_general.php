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
   
   private $barcodes;

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
      $this->barcodes = array("animal_id", "edta", "serum", "bsmear_1", "bsmear_2", "osmear_1", "osmear_2", "wing", "eparasite", "integument_bc", "pectoral_mc", "ptagium_bc", "saliva_1_bc", "saliva_2_bc", "saliva_3_bc", "diaphgram_bc", "liver_1_bc", "liver_2_bc", "liver_3_bc", "spleen_1_bc", "spleen_2_bc", "spleen_3_bc", "kidney_1_bc", "kidney_2_bc", "kidney_3_bc", "adrenal_bc", "heart_bc", "lung_1_bc", "lung_2_bc", "lung_3_bc", "pluck_bc", "urine_1_bc", "urine_2_bc", "femur_1_bc", "femur_2_bc", "brain_bc", "faeces_1_bc", "faeces_2_bc", "faeces_3_bc", "urogen_1_bc", "urogen_2_bc", "stomach_bc", "ileum_1_bc", "ileum_2_bc", "smallint_bc", "largeint_bc", "carcas_bc");
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
         elseif(OPTIONS_REQUESTED_SUB_MODULE == 'step20') $this->initPmStep20();
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
<a href="./?page=" style="float: right; margin-bottom: 10px; margin-right: 20px;">Home</a> <br />
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
         $prevCss = "background-color: #40BF80; border: 3px solid #2D8659;";
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
   $("#content_container").html("<?php echo $html;?>");
   var uzp = new Uzp("<?php echo $uri;?>", "<?php echo $lastInputId;?>");
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
            . $this->generateInputPair("Animal Id", "animal_id", $data, "barcode")
            . $this->generateSelectPair("Animal Class", "animal_class", array("rodent" => "Rodent", "bat" => "Bat"), $data)
            . "</div>";
      $this->initUZPJs("step1", $html, "animal_class_input", null, "step2", $_GET['animal']);
   }
   
   private function initPmStep2() {
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Samples</h3>"
            . "<div class='input_container'>"
            . $this->generateInputPair("Weight (grams)", "weight", $data, "number", null, null, null, array("min" =>0, "max" => 20000))
            . $this->generateInputPair("EDTA", "edta", $data, "barcode", null, null, "If enough blood")
            . $this->generateInputPair("Serum", "serum", $data, "barcode")
            . $this->generateInputPair("1st Blood Smear", "bsmear_1", $data, "barcode", null, null, "Dry 30 mins, then methanol")
            . $this->generateInputPair("2nd Blood Smear", "bsmear_2", $data, "barcode", null, null, "Dry 30 mins, then methanol")
            . $this->generateInputPair("1st Oropharyngeal", "osmear_1", $data, "barcode", null, null, "200ul Lysis buffer")
            . $this->generateInputPair("2nd Oropharyngeal", "osmear_2", $data, "barcode", null, null, "200ul VTM")
            . $this->generateInputPair("Wing biopsy", "wing", $data, "barcode", "animal_class", array("bat"), "0.5mL 97% ethanol, in 1mL cryovial")//only if bat
            . $this->generateInputPair("Ectoparasite", "eparasite", $data, "barcode", null, null, "97% ethanol")
            . "</div>";
      $this->initUZPJs("step2", $html, "eparasite_input", "step1", "step3", $_GET['animal']);
   }
   
   private function initPmStep3() {
      $data = $this->getAnimalData($_GET['animal']);
      $species = array("unknown" => "Unknown");
      $bcs = array("unknown" => "Unknown");
      $ages = array(" neonate" => "Neonate", "juvenile" => "Juvenile", "subadult" => "Subadult", "adult" => "Adult", "unknown" => "Unknown");
      if(is_array($data) && isset($data['animal_class'])) {
         if($data['animal_class'] == 'rodent') {
            $species = array(
               "unknown" => "Unknown", "rattus" => "Common Rat (Rattus)", "mastomys" => "Multimammate Rat (Mastomys)", "mus" => "Common Mouse (Mus)", "graphiurus" => "African Dormouse (Graphiurus)", "savannah cane rat" => "Savannah Cane-rat", "dendromus" => "Climbing Mouse (Dendromus)", "steatomys" => "Fat Mouse (Steatomys)", "cricetomys" => "Giant Pouched Rat (Cricetomys)", "saccostomus" => "Pouched Mouse (Saccostomus)", "lophuromys" => "Brush-furred Mouse (Lophuromys)", "arvicanthis" => "Unstriped Grass Rat (Arvicanthis)", "pelomys" => "Creek Rat (Pelomys)", "lemniscomys" => "Zebra Mouse (Lemniscomys)"
            );
            $bcs = array("1" => "1", "2" => "2", "3" => "3", "4" => "4", "5" => "5");
            $ages = array(" neonate" => "Neonate", "juvenile" => "Juvenile", "subadult" => "Subadult", "adult" => "Adult", "unknown" => "Unknown");
         }
         else if($data['animal_class'] == 'bat') {
            $species = array("unknown" => "Unknown", "eidolon helvum" => "Eidolon helvum", "lissonycerteris angolensis" => "Lissonycerteris angolensis", "micropteropus pusillus" => "Micropteropus pusillus", "rousettus aegyptiacus" => "Rousettus aegyptiacus", "epomorphus" => "Epomorphus", "epomorphus minimus" => "Epomorphus minimus", "epomorphus wahlbergi" => "Epomorphus wahlbergi", "rhinolophus" => "Rhinolophus", "rhinolophus clivosus" => "Rhinolophus clivosus", "rhinolophus eloquens" => "Rhinolophus eloquens", "rhinolophus fumigatus" => "Rhinolophus fumigatus", "rhinolophus hildebrandtii" => "Rhinolophus hildebrandtii", "rhinolophus landeri" => "Rhinolophus landeri", "rhinolophus simulator" => "Rhinolophus simulator", "hipposideridae" => "Hipposideridae", "triaenops persicus" => "Triaenops persicus", "hipposideros caffer" => "Hipposideros caffer", "hipposideros gigas" => "Hipposideros gigas", "hipposideros megalotis" => "Hipposideros megalotis", "hipposideros ruber" => "Hipposideros ruber", "hipposideros vittatus" => "Hipposideros vittatus", "megadermatidae" => "Megadermatidae", "cardioderma cor" => "Cardioderma cor", "lavia frons" => "Lavia frons", "rhinopomatidae" => "Rhinopomatidae", "rhinopoma macinnesi" => "Rhinopoma macinnesi", "emballonuridae" => "Emballonuridae", "taphozous perforatus" => "Taphozous perforatus", "nycteridae" => "Nycteridae", "nycteris aurita" => "Nycteris aurita", "nycteris grandis" => "Nycteris grandis", "nycteris hispida" => "Nycteris hispida", "nycteris macrotis" => "Nycteris macrotis", "nycteris thebaica" => "Nycteris thebaica", "molossidae" => "Molossidae", "platymops (genus)" => "Platymops (genus)", "platymops setiger" => "Platymops setiger", "chaerephon (genus)" => "Chaerephon (genus)", "chaerephon bemmeleni" => "Chaerephon bemmeleni", "chaerephon chapini" => "Chaerephon chapini", "mops (genus)" => "Mops (genus)", "mops condylurus" => "Mops condylurus", "tadarida (genus)" => "Tadarida (genus)", "tadarida aegyptiaca" => "Tadarida aegyptiaca", "tadarida lobata" => "Tadarida lobata", "miniopteridae" => "Miniopteridae", "miniopterus africanus" => "Miniopterus africanus", "miniopterus fraterculus" => "Miniopterus fraterculus", "miniopterus inflatus" => "Miniopterus inflatus", "miniopterus natalensis" => "Miniopterus natalensis", "vespertilionidae" => "Vespertilionidae", "mimetillus moloneyi" => "Mimetillus moloneyi", "nycticeinops schlieffeni" => "Nycticeinops schlieffeni", "glauconycteris (genus)" => "Glauconycteris (genus)", "glauconycteris argentata" => "Glauconycteris argentata", "glauconycteris variegata" => "Glauconycteris variegata", "hypsugo (genus)" => "Hypsugo (genus)", "hypsugo eisentrauti" => "Hypsugo eisentrauti", "pipistrellus (genus)" => "Pipistrellus (genus)", "pipistrellus aero" => "Pipistrellus aero", "pipistrellus grandidieri" => "Pipistrellus grandidieri", "pipistrellus hesperidus" => "Pipistrellus hesperidus", "kerivoula (genus)" => "Kerivoula (genus)", "kerivoula argentata" => "Kerivoula argentata", "kerivoula smithii" => "Kerivoula smithii", "myotis (genus)" => "Myotis (genus)", "myotis bocagii" => "Myotis bocagii", "myotis tricolor" => "Myotis tricolor", "myotis welwitschii" => "Myotis welwitschii", "neoromicia (genus)" => "Neoromicia (genus)", "neoromicia capensis" => "Neoromicia capensis", "neoromicia helios" => "Neoromicia helios", "neoromicia nana " => "Neoromicia nana ", "neoromicia somalica" => "Neoromicia somalica", "scotoecus (genus)" => "Scotoecus (genus)", "scotoecus albigula" => "Scotoecus albigula", "scotoecus hindei" => "Scotoecus hindei", "scotoecus hirundo" => "Scotoecus hirundo", "scotophilus" => "Scotophilus", "scotophilus nigrita" => "Scotophilus nigrita");
            $bcs = array("poor" => "Poor", "fair" => "Fair", "good" => "Good");
            $ages = array(" neonate" => "Neonate", "juvenile" => "Juvenile", "adult" => "Adult", "unknown" => "Unknown");
         }
      }
      $html = "<h3 class='center'>Phenotyping</h3>"
            . "<div class='input_container'>"
              . $this->generateSelectPair("Species", "species", $species, $data)
              . $this->generateTextAreaPair("Taxonomy to lowest level", "taxonomy", $data, "species", array('unknown'))
              . $this->generateSelectPair("ID Certainty", "id_certainty", array("actual" => "Actual", "estimate" => "Estimate", "unknown" => "Unknown"), $data)
              . $this->generateSelectPair("Age Class", "age", $ages, $data)
              . $this->generateSelectPair("Sex", "sex", array("male" => "Male", "female" => "Female", "unknown" => "Unknown"), $data)
              . $this->generateSelectPair("Pregnant?", "pregnant", array("yes" => "Yes", "no" => "No"), $data, "sex", array("female"))
              . $this->generateSelectPair("Lactating?", "lactating", array("yes" => "Yes", "no" => "No"), $data, "sex", array("female"))
              . $this->generateSelectPair("Condition at sampling", "cond_samp", array("a_healthy" => "Apparently healthy", "sign_sick" => "Signs of sickness", "injured" => "Injured", "unknown" => "Unknown"), $data)
              . $this->generateTextAreaPair("Describe clinical signs if present", "clcl_sgns", $data)
              . $this->generateSelectPair("Is disease suspected?", 'is_dis_suspected', array("yes" => "Yes", "no" => "No"), $data)
              . $this->generateInputPair("Suspected disease", "suspect_dis", $data, "text", "is_dis_suspected", array("yes"))
              . $this->generateSelectPair("Body condition score", "bcs", $bcs, $data)
            . "</div>";
      $this->initUZPJs("step3", $html, "bcs_input", "step2", "step4", $_GET['animal']);
   }
   
   private function initPmStep4() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $bodyLengthComment = "Rodent: Distance from tip of nose to tip of tail<br />Bat: Ventral recumbency from tip of nose to base of tail";
      $earLengthComment = "Rodent: Distance from notch at base of ear to distal portion of pinna (medial aspect)<br />Bat: Distal tip of ear to middle of base (Microchiroptera only)";
      $tragusComment = "Top of tragus to base of ear (Microchiroptera only)";
      $forearmComment = "Elbow to wrist";
      $tibiaComment = "Microchiroptera only";
      $hindFootComment = "Rodent: Distance from back of 'heel' to tip of long toe, excluding the claw<br />Bat: Ankle to toe  (Microchiroptera only)";
      $tailComment = "Microchiroptera only";
      $fullBodyComment = "Lateral: Rodent; Ventral (wings extended):Bat";
      if(is_array($data) && isset($data['animal_class']) && $data['animal_class'] == 'rodent') {
         $bodyLengthComment = "Distance from tip of nose to tip of tail";
         $earLengthComment = "Distance from notch at base of ear to distal portion of pinna (medial aspect)";
         $hindFootComment = "Distance from back of 'heel' to tip of long toe, excluding the claw";
         $fullBodyComment = "Lateral";
      }
      else if(is_array($data) && isset($data['animal_class']) && $data['animal_class'] == 'bat') {
         $bodyLengthComment = "Ventral recumbency from tip of nose to base of tail";
         $earLengthComment = "Distal tip of ear to middle of base (Microchiroptera only)";
         $hindFootComment = "Ankle to toe  (Microchiroptera only)";
         $fullBodyComment = "Ventral (wings extended)";
      }
      $html = "<h3 class='center'>Body Measurements</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Body length (mm)", "body_length", $data, "number", null, null, $bodyLengthComment, array("min" => 10, "max" => 1000))
              . $this->generateInputPair("Ear length (mm)", "ear_length",  $data, "number", null, null, $earLengthComment, array("min" => 5, "max" => 200))
              . $this->generateInputPair("Tragus length (mm)", "tragus_length", $data, "number", "animal_class", array("bat"), $tragusComment, array("min" =>1, "max" => 200))//only if bat
              . $this->generateInputPair("Forearm length (mm)", "forearm_length", $data, "number", "animal_class", array("bat"), $forearmComment, array("min" => 10, "max" => 300))//only if bat
              . $this->generateInputPair("Tibia length (mm)", "tibia_length", $data, "number", "animal_class", array("bat"), $tibiaComment, array("min" => 10, "max" => 200))//only if bat
              . $this->generateInputPair("Hind foot length (mm)", "hfoot_length", $data, "number", null, null, $hindFootComment, array("min" => 1, "max" => 300))
              . $this->generateInputPair("Tail length (mm)", "tail_length", $data, "number", null, null, $tailComment, array("min" => 10, "max" => 1000))
              . $this->generateInputPair("Full body", "full_body_length", $data, "number", null, null, $fullBodyComment)
              . $this->generateInputPair("Full anterior facial", "anterior_facial", $data, "number")
              . $this->generateInputPair("Full lateral facial/head", "lateral_facial", $data, "number")
              . $this->generateInputPair("Parted pelage on dorsum", "pp_dorsum", $data, "number", "animal_class", array("bat"))//only if bat
              . $this->generateInputPair("Parted pelage on vetrum", "pp_vetrum", $data, "number", "animal_class", array("bat"))//only if bat
              . "</div>";
      $this->initUZPJs("step4", $html, "pp_vetrum_input", "step3", "step5", $animalId);
   }
   
   private function initPmStep5() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Necropsy</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Integument lesions", "integument_les", $data)
              . $this->generateInputPair("Scan integument", "integument_bc", $data, "barcode", null, null, "10% formalin")
              . $this->generateTextAreaPair("Pectoral muscle lesions", "pectoral_les", $data)
              . $this->generateInputPair("Scan pectoral muscle", "pectoral_mc", $data, "barcode", null, null, "97% ethanol")
              . $this->generateTextAreaPair("Ptagium lesions", "ptagium_les", $data, "animal_class", array("bat"))//only if bat
              . $this->generateInputPair("Scan ptagium", "ptagium_bc", $data, "barcode", "animal_class", array("bat"), "97% ethanol")//only if bat
              . "</div>";
      $this->initUZPJs("step5", $html, "ptagium_bc_input", "step4", "step6", $animalId);//if bat then step6 else step7
   }
   
   private function initPmStep6() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Salivary Glands</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Salivary gland lesions", "saliva_les", $data, "animal_class", array("bat"))
              . $this->generateInputPair("Scan salivary glands 1", "saliva_1_bc", $data, "barcode", "animal_class", array("bat"), "Frozen")
              . $this->generateInputPair("Scan salivary glands 2", "saliva_2_bc", $data, "barcode", "animal_class", array("bat"), "VTM")
              . $this->generateInputPair("Scan salivary glands 3", "saliva_3_bc", $data, "barcode", "animal_class", array("bat"), "10% formalin")
              . "</div>";
      $this->initUZPJs("step6", $html, "saliva_3_bc_input", "step5", "step7", $animalId);
   }
   
   private function initPmStep7() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Step 7</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Cavity lesions", "cavity_les", $data)
              . $this->generateTextAreaPair("Diaphgram lesions", "diaphgram_les", $data)
              . $this->generateInputPair("Scan diaphgram", "diaphgram_bc", $data, "barcode", null, null, "10% formalin")
              . "</div>";
      $this->initUZPJs("step7", $html, "diaphgram_bc_input", "step6", "step8", $animalId);
   }
   
   private function initPmStep8() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Liver</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Liver lesions", "liver_les", $data)
              . $this->generateInputPair("Liver weight (mg)", "liver_weight", $data, "number", null, null, null, array("min" => 1, "max" => 10000))
              . $this->generateInputPair("Scan liver 1", "liver_1_bc", $data, "barcode", null, null, "Frozen")
              . $this->generateInputPair("Scan liver 2", "liver_2_bc", $data, "barcode", null, null, "VTM")
              . $this->generateInputPair("Scan liver 3", "liver_3_bc", $data, "barcode", null, null, "10% formalin")
              . "</div>";
      $this->initUZPJs("step8", $html, "liver_3_bc_input", "step7", "step9", $animalId);
   }
   
   private function initPmStep9() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Spleen</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Spleen lesions", "spleen_les", $data)
              . $this->generateInputPair("Spleen weight (mg)", "spleen_weight", $data, "number", null, null, null, array("min" => 1, "max" => 10000))
              . $this->generateInputPair("Scan spleen 1", "spleen_1_bc", $data, "barcode", null, null, "Frozen")
              . $this->generateInputPair("Scan spleen 2", "spleen_2_bc", $data, "barcode", null, null, "VTM")
              . $this->generateInputPair("Scan spleen 3", "spleen_3_bc", $data, "barcode", null, null, "10% formalin")
              . "</div>";
      $this->initUZPJs("step9", $html, "spleen_3_bc_input", "step8", "step10", $animalId);
   }
   
   private function initPmStep10() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Kidney</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Kidney lesions", "kidney_les", $data)
              . $this->generateInputPair("Kidney weight (mg)", "kidney_weight", $data, "number", null, null, null, array("min" => 1, "max" => 10000))
              . $this->generateInputPair("Scan kidney 1", "kidney_1_bc", $data, "barcode", null, null, "Frozen")
              . $this->generateInputPair("Scan kidney 2", "kidney_2_bc", $data, "barcode", null, null, "VTM")
              . $this->generateInputPair("Scan kidney 3", "kidney_3_bc", $data, "barcode", null, null, "10% fromalin")
              . "</div>";
      $this->initUZPJs("step10", $html, "kidney_3_bc_input", "step9", "step11", $animalId);
   }
   
   private function initPmStep11() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Adrenal</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Adrenal lesions", "adrenal_les", $data)
              . $this->generateInputPair("Adrenal weight (mg)", "adrenal_weight", $data, "number", null, null, null, array("min" => 1, "max" => 10000))
              . $this->generateInputPair("Scan adrenal", "adrenal_bc", $data, "barcode")
              . "</div>";
      $this->initUZPJs("step11", $html, "adrenal_bc_input", "step10", "step12", $animalId);
   }
   
   private function initPmStep12() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Heart</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Heart lesions", "heart_les", $data)
              . $this->generateInputPair("Heart weight", "heart_weight", $data, "number", null, null, null, array("min" => 1, "max" => 10000))
              . $this->generateInputPair("Scan heart", "heart_bc", $data, "barcode", null, null, "Frozen")
              . "</div>";
      $this->initUZPJs("step12", $html, "heart_bc_input", "step11", "step13", $animalId);
   }
   
   private function initPmStep13() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Lung</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Lung lesions", "lung_les", $data)
              . $this->generateInputPair("Lung weight", "lung_weight", $data, "number", null, null, null, array("min" => 1, "max" => 10000))
              . $this->generateInputPair("Scan lung 1", "lung_1_bc", $data, "barcode", null, null, "Frozen")
              . $this->generateInputPair("Scan lung 2", "lung_2_bc", $data, "barcode", null, null, "VTM")
              . $this->generateInputPair("Scan lung 3", "lung_3_bc", $data, "barcode", null, null, "10% formalin")
              . "</div>";
      $this->initUZPJs("step13", $html, "lung_3_bc_input", "step12", "step14", $animalId);
   }
   
   private function initPmStep14() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Step 14</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Pluck lesions", "pluck_les", $data)
              . $this->generateInputPair("Scan pluck", "pluck_bc", $data, "barcode", null, null, "10% formalin")
              . $this->generateInputPair("Scan urine", "urine_1_bc", $data, "barcode", null, null, "Lysis buffer")
              . $this->generateInputPair("Scan urine", "urine_2_bc", $data, "barcode", null, null, "VTM")
              . $this->generateInputPair("Scan femur", "femur_1_bc", $data, "barcode", null, null, "VTM")
              . $this->generateInputPair("Scan femur", "femur_2_bc", $data, "barcode", null, null, "10% formalin")
              . "</div>";
      $this->initUZPJs("step14", $html, "femur_2_bc_input", "step13", "step15", $animalId);
   }
   
   private function initPmStep15() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Brain</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Brain lesions", "brain_les", $data)
              . $this->generateInputPair("Brain weight", "brain_weight", $data, "number", null, null, null, array("min" => 1, "max" => 20000))
              . $this->generateInputPair("Scan brain", "brain_bc", $data, "barcode", null, null, "Frozen")
              . "</div>";
      $this->initUZPJs("step15", $html, "brain_bc_input", "step14", "step16", $animalId);
   }
   
   private function initPmStep16() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Faeces</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Scan faeces 1", "faeces_1_bc", $data, "barcode", null, null, "Frozen")
              . $this->generateInputPair("Scan faeces 2", "faeces_2_bc", $data, "barcode", null, null, "VTM")
              . $this->generateInputPair("Scan faeces 3", "faeces_3_bc", $data, "barcode", null, null, "Fresh (to lab in transport media)")
              . "</div>";
      $this->initUZPJs("step16", $html, "faeces_3_bc_input", "step15", "step17", $animalId);
   }
   
   private function initPmStep17() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Urogenital</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Urogenital lesions", "urogen_les", $data)
              . $this->generateInputPair("Scan urogenital", "urogen_1_bc", $data, "barcode", null, null, "VTM")
              . $this->generateInputPair("Scan urogenital", "urogen_2_bc", $data, "barcode", null, null, "10% formalin")
              . "</div>";
      $this->initUZPJs("step17", $html, "urogen_2_bc_input", "step16", "step18", $animalId);
   }
   
   private function initPmStep18() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Stomach & Ileum</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Stomach lesions", "stomach_les", $data)
              . $this->generateInputPair("Scan stomach", "stomach_bc", $data, "barcode", null, null, "10% formalin")
              . $this->generateTextAreaPair("Ileum lesions", "ileum_les", $data)
              . $this->generateInputPair("Scan ileum 1", "ileum_1_bc", $data, "barcode", null, null, "Frozen")
              . $this->generateInputPair("Scan ileum 2", "ileum_2_bc", $data, "barcode", null, null, "VTM")
              . "</div>";
      $this->initUZPJs("step18", $html, "ileum_2_bc_input", "step17", "step19", $animalId);
   }
   
   private function initPmStep19() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Intestines</h3>"
              . "<div class='input_container'>"
              . $this->generateTextAreaPair("Small intestines lesions", "smallint_les", $data)
              . $this->generateInputPair("Scan small intestines", "smallint_bc", $data, "barcode", null, null, "10% formalin")
              . $this->generateTextAreaPair("Large intestines lesions", "largeint_les", $data)
              . $this->generateInputPair("Scan large intestines", "largeint_bc", $data, "barcode", null, null, "10% formalin")
              . "</div>";
      $this->initUZPJs("step19", $html, "largeint_bc_input", "step18", "step20", $animalId);
   }
   
   private function initPmStep20() {
      $animalId = $_GET['animal'];
      $data = $this->getAnimalData($_GET['animal']);
      $html = "<h3 class='center'>Carcass</h3>"
              . "<div class='input_container'>"
              . $this->generateInputPair("Carcass barcode", "carcas_bc", $data, "barcode")
              . $this->generateTextAreaPair("General Comment", "general_comment", $data)
              . "</div>";
      $this->initUZPJs("step20", $html, "carcas_bc_input", "step19", null, $animalId);
   }
   
   private function generateInputPair($label, $id, $data = null, $type = 'text', $dependsOn = null, $possibleValues = null, $comment = null, $bounds = null) {
      $extraStyle = "";
      if($type == 'barcode') {
         $idSelect = "";
         if($data != null) $idSelect = " and id != ".$data['id'];
         $extraStyle .= " barcode-input";
         $type = 'text';
         //make the barcode follows the syntax for the previous barcode
         $query = "select $id from postmortem where $id != '' $idSelect order by id desc limit 1";//fetch the last barcode
         $result = $this->Dbase->ExecuteQuery($query);
         if(is_array($result) && count($result) == 1) {
            $lastBarcode = $result[0][$id];
?>
<script type="text/javascript">
   $(document).ready(function(){
      window.uzp_lab.addRule("<?php echo $id;?>", 'regex', "<?php echo $lastBarcode;?>");
   });
</script>
<?php
         }
      }
      else if($type == 'number' && $bounds != null){
?>
<script type="text/javascript">
   $(document).ready(function() {
      window.uzp_lab.addRule("<?php echo $id;?>", 'bounds', <?php echo json_encode($bounds);?>);
   });
</script>
<?php
      }
      $defaultValue = '';
      $disabled = '';
      if($data != null){
         if(isset($data[$id])) $defaultValue = $data[$id];
         if($dependsOn != null && isset($data[$dependsOn])) {
            if(in_array($data[$dependsOn], $possibleValues)) {//depends on value is in the possible values
               $disabled = '';
            }
            else {
               $disabled = 'disabled';
            }
         }
      }
      if($dependsOn != null) {
         //set on javascript
?>
<script type="text/javascript">
   $(document).ready(function(){
      window.uzp_lab.setDependsOn("<?php echo $id;?>", "<?php echo $dependsOn;?>", <?php echo json_encode($possibleValues);?>);
   });
</script>
<?php
      }
      $input_id = $id."_input";
      $html = "<div id='$id'><label class='input_label'>$label</label>&nbsp;&nbsp;<input type='$type' id='$input_id' name='$input_id' class='$extraStyle input-medium' value='$defaultValue' $disabled /></div>";
      if($comment != null) {
         $html .= "<div class='input_comment'>$comment</div>";
      }
      return $html;
   }
   
   private function generateSelectPair($label, $id, $options, $data = null, $dependsOn = null, $possibleValues = null, $comment = null) {
      $defaultValue = '';
      $disabled = '';
      if($data != null){
         if(isset($data[$id])) $defaultValue = $data[$id];
         if($dependsOn != null && isset($data[$dependsOn])) {
            if(in_array($data[$dependsOn], $possibleValues)) {//depends on value is in the possible values
               $disabled = '';
            }
            else {
               $disabled = 'disabled';
            }
         }
      }
      if($dependsOn != null) {
         //set on javascript
?>
<script type="text/javascript">
   $(document).ready(function(){
      console.log("Document ready");
      window.uzp_lab.setDependsOn("<?php echo $id;?>", "<?php echo $dependsOn;?>", <?php echo json_encode($possibleValues);?>);
   });
</script>
<?php
      }
      $input_id = $id."_input";
      $html = "<div id='$id'><label class='input_label'>$label</label>&nbsp;&nbsp;<select id='$input_id' name='$input_id' $disabled>";
      $html .= "<option value=''></option>";
      $optionValues = array_keys($options);
      foreach($optionValues as $currOption) {
         $selected = '';
         if($defaultValue == $currOption) $selected = "selected";
         $html .= "<option value='$currOption' $selected>".$options[$currOption]."</option>";
      }
      $html .= "</select></div>";
      if($comment != null) {
         $html .= "<div class='input_comment'>$comment</div>";
      }
      return $html;
   }
   
   private function generateTextAreaPair($label, $id, $data = null, $dependsOn = null, $possibleValues = null, $comment = null) {
      $defaultValue = '';
      $disabled = '';
      if($data != null){
         if(isset($data[$id])) $defaultValue = $data[$id];
         if($dependsOn != null && isset($data[$dependsOn])) {
            if(in_array($data[$dependsOn], $possibleValues)) {//depends on value is in the possible values
               $disabled = '';
            }
            else {
               $disabled = 'disabled';
            }
         }
      }
      if($dependsOn != null) {
         //set on javascript
?>
<script type="text/javascript">
   $(document).ready(function(){
      window.uzp_lab.setDependsOn("<?php echo $id;?>", "<?php echo $dependsOn;?>", <?php echo json_encode($possibleValues);?>);
   });
</script>
<?php
      }
      $input_id = $id."_input";
      $html = "<div id='$id'><label class='input_label'>$label</label>&nbsp;&nbsp;<textarea cols='4' rows='2' id='$input_id' name='$input_id' $disabled>$defaultValue</textarea></div>";
      if($comment != null) {
         $html .= "<div class='input_comment'>$comment</div>";
      }
      return $html;
   }
   
   private function commitStepData() {
      $response = array();
      $currStep = $_GET['curr_step'];
      $animalId = $_GET['animal'];
      $direction = $_GET['direction'];
      $now = new DateTime('now');
      $postFields = array_keys($_POST);
      $postData = array();
      $allBarcodes = $allBarcodes = $this->getAnimalBarcodes();
      foreach($postFields as $currField) {
         if(in_array($currField, $this->barcodes)) {//current field stores barcodes
            $allBarcodes = $this->validateBarcode($animalId, $currField, $_POST[$currField], $allBarcodes);
            if($allBarcodes == null) {//barcode already exists
               $response['error'] = true;
               $response['message'] = "Barcode is not unique";
               $response['focus'] = $currField;
               die(json_encode($response));
            }
         }
         $postData[$currField] = $_POST[$currField];
         if(!$this->hasValue($postData[$currField]))$postData[$currField] = null;
      }
      $nowTime = $now->format('Y-m-d H:i:s');
      $postFields[] = "end_time";
      $postData['end_time'] = $nowTime;
      if($this->hasValue($animalId) == false && $currStep == "step1" && $direction == "next") {//the first step. We really dont expect to have an animal id at this point
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
         $result = $this->Dbase->ExecuteQuery($query, $postData);
         if($result !== 1) {
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
         else {
            $response['error'] = true;
            $response['message'] = $this->Dbase->lastError;
         }
      }
      else if($this->hasValue($animalId)) {//the animal id should be set for all other steps
         $query = "update postmortem set ";
         for($index = 0; $index < count($postFields); $index++) {
            if($index < (count($postFields) - 1))  $query .= $postFields[$index]." = :".$postFields[$index].", ";
            else $query .= $postFields[$index]." = :".$postFields[$index]." where ";
         }
         $query .= "id = :animal";
         $postData['animal'] = $animalId;
         $result = $this->Dbase->ExecuteQuery($query, $postData);
         if($result !== 1) {
            $response['error'] = false;
            $response['message'] = "Previous step committed";
            $response['animal'] = $animalId;
            //check if we are in the last step
            if($currStep == "step20" && $direction == "next") {
               $mandatory = array("vet", "assistant", "animal_id", "animal_class", "weight", "species", "id_certainty", "age", "sex", "cond_samp", "is_dis_suspected", "bcs", "body_length", "ear_length", "hfoot_length", "tail_length", "full_body_length", "anterior_facial", "lateral_facial");
               $query = "select * from postmortem where id = :animal";
               $result = $this->Dbase->ExecuteQuery($query, array("animal" => $animalId));
               if(is_array($result) && count($result) == 1) {
                  $result = $result[0];
                  $ok = true;
                  foreach($mandatory as $currField) {
                     if($this->hasValue($result[$currField]) == false) {
                        $response['error'] = true;
                        $response['message'] = "$currField does not have a value";
                        $ok = false;
                        break;
                     }
                  }
                  if($ok == true) {
                     $query = "update postmortem set _complete = 1 where id = :animal";
                     $this->Dbase->ExecuteQuery($query, array("animal" => $animalId));
                  }
               }
               else {
                  $response['error'] = true;
                  $response['message'] = "Cannot find animal with the provided data";
               }
            }
         }
         else {
            $response['error'] = true;
            $response['message'] = $this->Dbase->lastError;
         }
      }
      else {
         $response['error'] = true;
         $response['message'] = "The animal ID is not set. Start postmoterm from the beginning";
      }
      die(json_encode($response));
   }
   
   private function hasValue($variable) {
      if(strlen($variable) > 0 && strtolower($variable) != "null") {
         return true;
      }
      return false;
   }
   
   private function getAnimalData($id) {
      $query = "select * from postmortem where id = :id";
      $result = $this->Dbase->ExecuteQuery($query, array("id" => $id));
      if(is_array($result) && count($result) == 1) return $result[0];
      return null;
   }
   
   private function getAnimalBarcodes() {
      $query = "select id";
      for($index = 0; $index < count($this->barcodes); $index++) {
         $query .= ", " . $this->barcodes[$index];
      }
      $query .= " from postmortem";
      $barcodes = array();
      $result = $this->Dbase->ExecuteQuery($query, array("animal" => $animalId));
      if(is_array($result) && count($result) > 0) {
         foreach($result as $currResult) {
            $barcodes[$currResult['id']] = array();
            $keys = array_keys($currResult);
            foreach($keys as $currKey) {
               if($currKey != "id" && strlen($currResult[$currKey]) > 0) $barcodes[$currResult['id']][$currKey] = $currResult[$currKey];
            }
         }
      }
      return $barcodes;
   }
   
   private function validateBarcode($animalId, $field, $barcode, $otherBarcodes = null) {
      if($otherBarcodes == null) $otherBarcodes = $this->getAnimalBarcodes();
      if($this->hasValue($barcode)) {
         $allAnimalIds = array_keys($otherBarcodes);
         foreach($allAnimalIds as $currCompAnimal) {
            $allFields = array_keys($otherBarcodes[$currCompAnimal]);
            foreach($allFields as $currCompField) {
               if(strtolower($otherBarcodes[$currCompAnimal][$currCompField]) == strtolower($barcode)) {//there's a match
                  if($currCompAnimal != $animalId || $currCompField != $field) return null;//the barcode is from another field
               }
            }
         }
         if(!isset($otherBarcodes[$animalId])) $otherBarcodes[$animalId] = array();
         $otherBarcodes[$animalId][$field] = $barcode;
      }
      return $otherBarcodes;
   }
}
?>