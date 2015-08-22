/**
 * The constructor of the Uzp functionality
 *
 * @returns {Uzp}
 */
function Uzp(currUri, currHtml) {
   console.log(currHtml);
   window.uzp_lab = this;

   // initialize the main variables
   window.uzp_lab.sub_module = Common.getVariable('do', document.location.search.substring(1));
   window.uzp_lab.module = Common.getVariable('page', document.location.search.substring(1));

   this.serverURL = "./modules/mod_uzp_lab_general.php";
   this.procFormOnServerURL = "mod_ajax.php";

   // create the notification place
   $("#notification_box").jqxNotification({ width: 250, position: "top-right", opacity: 0.9, autoOpen: false, animationOpenDelay: 800, autoClose: true, autoCloseDelay: 3000, template: "info", showCloseButton: false });
   this.prevNotificationClass = 'info';
   this.currUri = currUri;
   this.currHtml = currHtml;
   this.prevUri = null;
   this.nextUri = null;
   
   $("#content_container").html(currHtml);
};

Uzp.prototype.setPrevStep = function(prevStepUri) {
   window.uzp_lab.prevUri = prevStepUri;
};

Uzp.prototype.setNextStep = function(nextStepUri) {
   window.uzp_lab.nextUri = nextStepUri;
};

/**
 * Show a notification on the page
 *
 * @param   message     The message to be shown
 * @param   type        The type of message
 */
Uzp.prototype.showNotification = function(message, type){
   if(type === undefined) { type = 'error'; }

   $('#notification_box #msg').html(message);

   $('#notification_box').removeClass('jqx-notification-'+uzp.prevNotificationClass);
   $('#notification_box').addClass('jqx-notification-'+type);

   $('table td:first').removeClass('jqx-notification-icon-'+uzp.prevNotificationClass);
   $('table td:first').addClass('jqx-notification-icon-'+type);

   $('#notification_box').jqxNotification('open');
   uzp.prevNotificationClass = type;
};

Uzp.prototype.biochemTestLogic = function() {
   $("#res1").hide();
   $("#res2").hide();
   $("#res3").hide();
   $("#res1").find('br').remove();
   $("#res2").find('br').remove();
   $("#res3").find('br').remove();
   $("[name=res1_select]").remove();
   $("[name=res2_select]").remove();
   $("[name=res3_select]").remove();
   $("[class=test_oblabels]").remove();
   if($("#testId").val() == "tsi") {
      /* Test 1: slant color
       * Test 2: butt color
       * Test 3: gas present
       */
      $("#res1").show();
      $("#res1_label").html("Slant color");
      $("#res1").append(uzp.createOptionList("res1_select", ["Yellow", "Not Yellow"], ["yellow", "not_yellow"]));
      $("#res2").show();
      $("#res2_label").html("Butt color");
      $("#res2").append(uzp.createOptionList("res2_select", ["Yellow", "Black", "Other"], ["yellow", "black", "other"]));
      $("#res3").show();
      $("#res3_label").html("Gas present");
      $("#res3").append(uzp.createOptionList("res3_select", ["Yes", "No"], ["yes", "no"]));
   }
   if($("#testId").val() == "urea") {
      /* Test 1: Color
       */
      $("#res1").show();
      $("#res1_label").html("Color");
      $("#res1").append(uzp.createOptionList("res1_select", ["Yellow", "Not Yellow"], ["yellow", "not_yellow"]));
   }
   if($("#testId").val() == "mio") {
      /* Test 1: Motile
       * Test 2: Color
       */
      $("#res1").show();
      $("#res1_label").html("Motile");
      $("#res1").append(uzp.createOptionList("res1_select", ["Yes", "No"], ["yes", "no"]));
      $("#res2").show();
      $("#res2_label").html("Color");
      $("#res2").append(uzp.createOptionList("res2_select", ["Pink", "Not pink"], ["pink", "not_pink"]));
   }
   if($("#testId").val() == "citrate") {
      /* Test 1: Growth
       * Test 2: Color
       */
      $("#res1").show();
      $("#res1_label").html("Growth");
      $("#res1").append(uzp.createOptionList("res1_select", ["Yes", "No"], ["yes", "no"]));
      $("#res2").show();
      $("#res2_label").html("Color");
      $("#res2").append(uzp.createOptionList("res2_select", ["Green", "Not green"], ["green", "not_green"]));
   }
};

Uzp.prototype.createOptionList = function(commonName, optionLabels, optionValues) {
   var html = "<input type='radio' name='"+commonName+"' value='' style='display:none;' checked='checked' />";
   if(optionLabels.length == optionValues.length) {
      for(var index = 0; index < optionLabels.length; index++) {
         html = html + "<input type='radio' name='"+commonName+"' value='"+optionValues[index]+"' /><div class='test_oblabels'>"+optionLabels[index]+"</div>";
      }
   }
   html = html + "<br />";
   return html;
};

Uzp.prototype.saveReceivedSample = function(){
   // get the sample format and the received sample
   var format = $('[name=sample_format]').val(), sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val();
   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(format === '' || format === undefined){
      uzp.showNotification('Please a sample of the field sample to expect. Expecting a barcode like AVAQ63847', 'error');
      $("[name=sample_format]").focus();
      return;
   }
   if(uzp.fieldSampleRegex === undefined){
      //lets create the sample regex format
      var prefix = format.match(/^([a-z]+)/i);
      var suffix = format.match(/([0-9]+)$/i);
      uzp.fieldSampleRegex = '^'+prefix[0]+'[0-9]{'+suffix[0].length+'}$';
   }
   var regex = new RegExp(uzp.fieldSampleRegex, 'i');

   // lets ensure that our sample is in the right format
   if(regex.test(sample) === false){
      uzp.showNotification('Error! Unknown sample type!', 'error');
      $('[name=sample]').focus().val('');
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }

   // seems all is well, lets save the sample
   $.ajax({
      type:"POST", url: "mod_ajax.php?page=step1&do=save", async: false, dataType:'json', data: {format: format, sample: sample, cur_user: cur_user},
      success: function (data) {
         if(data.error === true){
            uzp.showNotification(data.mssg, 'error');
            $("[name=sample]").focus('').focus();
            return;
         }
         else{
            // we have saved the sample well... lets prepare for the next sample
            $("[name=sample]").focus();
            $("[name=sample]").val('');
            var currentdate = new Date();
            var datetime = " @ " + currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
            $('#receive_samples .saved').prepend(sample +' - '+ datetime+ "<br />");
         }
     }
  });
};

Uzp.prototype.receiveSampleKeypress = function(event){
   var keycode = (event.keyCode ? event.keyCode : event.which);
	if(keycode === 13){
		// an enter was pressed, lets save this particular sample
      if(uzp_lab.module === 'step1') uzp.saveReceivedSample();
      else if(uzp_lab.module === 'step2') uzp.saveBrothSample();
      else if(uzp_lab.module === 'step3') uzp.saveMcConkyPlate();
      else if(uzp_lab.module === 'step4') uzp.saveColonies();
      else if(uzp_lab.module === 'step5.1') uzp.saveColoniesPositions();
      else if(uzp_lab.module === 'step5') {
         if($("[name=sample]").is(":focus")) {
            uzp.savePlate2();
         }
         else {
            $(this).next().focus();
         }
      }
      else if(uzp_lab.module === 'step6') {
         if($("[name=sample]").is(":focus")) {
            uzp.saveBioChemPrep();
         }
         else {
            $(this).next().focus();
         }
      }
      else if(uzp_lab.module === 'step7') {
         if($("[name=sample]").is(":focus")) {
            uzp.saveBioChemResult();
         }
         else {
            $(this).next().focus();
         }
      }
      else if(uzp_lab.module === 'step8') {
         if($("[name=sample]").is(":focus")) {
            uzp.savePlate3();
         }
         else {
            $(this).next().focus();
         }
      }
      else if(uzp_lab.module === 'step9') {
         if($("[name=sample]").is(":focus")) {
            uzp.savePlate3to45();
         }
         else {
            $(this).next().focus();
         }
      }
      else if(uzp_lab.module === 'step10') {
         if($("[name=sample]").is(":focus")) {
            uzp.saveAstResult();
         }
         else {
            $(this).next().focus();
         }
      }
      else if(uzp_lab.module === 'step11') {
         if($("[name=sample]").is(":focus")) {
            uzp.saveRegrow();
         }
         else {
            $(this).next().focus();
         }
      }
      else if(uzp_lab.module === 'step12') {
         if($("[name=sample]").is(":focus")) {
            uzp.savePlateToEppendorfs();
         }
         else {
            $(this).next().focus();
         }
      }
      else if(uzp_lab.module === 'step13') {
         if($("[name=sample]").is(":focus")) {
            uzp.saveDnaArchiving();
         }
         else {
            $(this).next().focus();
         }
      }
	}
};

/**
 * Given a sample barcode, automatically generate the regex that will be used to validate the expected samples
 *
 * @param   {string}    sampleBarcode
 * @returns {RegExp}    Returns the created regex
 */
Uzp.prototype.createSampleRegex = function(sampleBarcode){
   var prefix = sampleBarcode.match(/^([a-z]+)/i);
   var suffix = sampleBarcode.match(/([0-9]+)$/i);
   var regex_f = '^'+prefix[0]+'[0-9]{'+suffix[0].length+'}$';
   var newRegex = new RegExp(regex_f, 'i');

   return newRegex;
};

Uzp.prototype.validateScannedSamples = function(settings){
   // check which kind of sample combinations we have here
   if(uzp.prevSampleType === undefined || uzp.curSampleType === undefined){
      uzp.showNotification('One sample type is not defined, so get the next sample... Current association of: ' +uzp.prevSample+ ' & '+uzp.curSample, 'mail');
      $("[name=sample]").focus().val('');
      return 1;  // one of the sample type is not defined... so lets return
   }
   else if(uzp.prevSampleType === settings.secondSample && uzp.curSampleType === settings.secondSample){
      // discard this and start afresh
      uzp.prevSampleType = undefined; uzp.curSampleType = undefined;
      uzp.prevSample = undefined; uzp.curSample = undefined;
      $("[name=sample]").focus().val('');
      return;
   }
   else if(uzp.prevSampleType === settings.firstSample && uzp.curSampleType === settings.secondSample){
      // save this association
      uzp.showNotification('Saving this association of parent ==> ' +uzp.prevSample+ ' and broth ==> '+uzp.curSample, 'success');
      return 0;
   }
   else{
      // we have some other permutation.. so keep going
      uzp.showNotification('Got the current association of parent ==> ' +uzp.prevSample+ ' and broth ==> '+uzp.curSample+', so continue scanning.', 'mail');
      $("[name=sample]").focus().val('');
      return 1;
   }
};

Uzp.prototype.saveBrothSample = function(){
   // get the sample format and the received sample
   var sample_format = $('[name=sample_format]').val(), broth_format = $('[name=broth_format]').val(), sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), curSampleType = undefined;

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(broth_format === '' || broth_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the broth. It should be something like \'BSR010959\'', 'error');
      $("[name=sample_format]").focus();
      return;
   }
   if(sample_format === '' || sample_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the field sample. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=sample_format]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }

   //lets validate the aliquot format
   var s_regex = uzp.createSampleRegex(sample_format);
   var b_regex = uzp.createSampleRegex(broth_format);

   // check whether we are dealing with the field or broth sample
   if(s_regex.test(sample) === true){ curSampleType = 'field_sample'; }          // we have a field sample
   else if(b_regex.test(sample) === true){ curSampleType = 'broth_sample'; }     // we have a broth sample
   else{
      // we don't know the sample format...so reject it and invalidate all the other settings
      uzp.showNotification('Error! Unknown format for the entered sample.'+sample, 'error');
      $("[name=sample]").focus().val('');
      uzp.prevSampleType = undefined; uzp.curSampleType = undefined;
      uzp.prevSample = undefined; uzp.curSample = undefined;
      return;
   }

   uzp.prevSampleType = uzp.curSampleType;
   uzp.curSampleType = curSampleType;
   uzp.prevSample = uzp.curSample;  // move the previous current sample to the previous sample
   uzp.curSample = sample;

   var res = uzp.validateScannedSamples({firstSample: 'field_sample', secondSample: 'broth_sample'});
   if(res === 1){ return; }

   // seems all is well, lets save the sample
   $.ajax({
      type:"POST", url: "mod_ajax.php?page=step2&do=save", async: false, dataType:'json', data: {field_sample: uzp.prevSample, broth_sample: uzp.curSample, cur_user: cur_user},
      success: function (data) {
         if(data.error === true){
            uzp.showNotification(data.mssg, 'error');
            $("[name=sample]").focus().val('');
            return;
         }
         else{
            // we have saved the sample well... lets prepare for the next sample
            $("[name=sample]").focus().val('');
            var currentdate = new Date();
            var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
            $('.received .saved').prepend(datetime +': '+ uzp.prevSample +'=>'+uzp.curSample +"<br />");
         }
     }
  });
};

Uzp.prototype.saveMcConkyPlate = function(){
   // check for the pre-requisites
   var plate_format = $('[name=plate_format]').val(), broth_format = $('[name=broth_format]').val();
   var sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), curSampleType = undefined, media_used = $('#mediaId').val();

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(broth_format === '' || broth_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the broth. It should be something like \'BSR010959\'', 'error');
      $("[name=broth_format]").focus();
      return;
   }
   if(plate_format === '' || plate_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the plate barcode. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=plate_format]").focus();
      return;
   }
   if(media_used === '0'){
      uzp.showNotification('Please select the media being used.', 'error');
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }

   //lets validate the samples
   var p_regex = uzp.createSampleRegex(plate_format);
   var b_regex = uzp.createSampleRegex(broth_format);

   // check whether we are dealing with the field or broth sample
   if(p_regex.test(sample) === true){ curSampleType = 'plate'; }                  // we have a mcconky plate
   else if(b_regex.test(sample) === true){ curSampleType = 'broth_sample'; }     // we have a broth sample
   else{
      // we don't know the sample format...so reject it and invalidate all the other settings
      uzp.showNotification('Error! Unknown format for the entered sample.'+sample, 'error');
      $("[name=sample]").focus().val('');
      uzp.prevSampleType = undefined; uzp.curSampleType = undefined;
      uzp.prevSample = undefined; uzp.curSample = undefined;
      return;
   }

   uzp.prevSampleType = uzp.curSampleType;
   uzp.curSampleType = curSampleType;
   uzp.prevSample = uzp.curSample;  // move the previous current sample to the previous sample
   uzp.curSample = sample;

   var res = uzp.validateScannedSamples({firstSample: 'broth_sample', secondSample: 'plate'});
   if(res === 1){ return; }

   // seems all is well, lets save the sample
   $.ajax({
      type:"POST", url: "mod_ajax.php?page=step3&do=save", async: false, dataType:'json', data: {broth_sample: uzp.prevSample, plate_barcode: uzp.curSample, cur_user: cur_user, media_used: media_used},
      success: function (data) {
         if(data.error === true){
            uzp.showNotification(data.mssg, 'error');
            $("[name=sample]").focus().val('');
            return;
         }
         else{
            // we have saved the sample well... lets prepare for the next sample
            $("[name=sample]").focus().val('');
            var currentdate = new Date();
            var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
            $('.received .saved').prepend(datetime +': '+ uzp.prevSample +'=>'+uzp.curSample +"<br />");
         }
     }
  });
};

Uzp.prototype.savePlate2 = function(){
   // get the sample format and the received sample
   var colony_format = $('[name=colony_format]').val(), plate_format = $('[name=plate_format]').val(), sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), curSampleType = undefined;

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(plate_format === '' || plate_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the broth. It should be something like \'BSR010959\'', 'error');
      $("[name=colony_format]").focus();
      return;
   }
   if(colony_format === '' || colony_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the field sample. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=colony_format]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }

   //lets validate the aliquot format
   var s_regex = uzp.createSampleRegex(colony_format);
   var b_regex = uzp.createSampleRegex(plate_format);

   // check whether we are dealing with the field or broth sample
   if(s_regex.test(sample) === true){
      // we have a field sample
      curSampleType = 'field_sample';
   }
   else if(b_regex.test(sample) === true){
      // we have a broth sample
      curSampleType = 'broth_sample';
   }
   else{
      // we don't know the sample format...so reject it and invalidate all the other settings
      uzp.showNotification('Error! Unknown format for the entered sample.'+sample, 'error');
      $("[name=sample]").focus().val('');
      uzp.prevSampleType = undefined; uzp.curSampleType = undefined;
      uzp.prevSample = undefined; uzp.curSample = undefined;
      return;
   }

   uzp.prevSampleType = uzp.curSampleType;
   uzp.curSampleType = curSampleType;
   uzp.prevSample = uzp.curSample;  // move the previous current sample to the previous sample
   uzp.curSample = sample;

   var res = uzp.validateScannedSamples({firstSample: 'field_sample', secondSample: 'broth_sample'});
   if(res === 1){ return; }

   // seems all is well, lets save the sample
   $.ajax({
      type:"POST", url: "mod_ajax.php?page=step5&do=save", async: false, dataType:'json', data: {plate_format: b_regex, colony_format: s_regex, field_sample: uzp.prevSample, broth_sample: uzp.curSample, cur_user: cur_user},
      success: function (data) {
         if(data.error === true){
            uzp.showNotification(data.mssg, 'error');
            $("[name=sample]").focus().val('');
            return;
         }
         else{
            // we have saved the sample well... lets prepare for the next sample
            $("[name=sample]").focus().val('');
            var currentdate = new Date();
            var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
            $('.received .saved').prepend(datetime +': '+ uzp.prevSample +'=>'+uzp.curSample +"<br />");
         }
     }
  });
};

Uzp.prototype.saveBioChemResult = function(){
   // get the sample format and the received sample
   var sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), curSampleType = undefined, test_name = $('#testId').val(), test_result = $('#testResultId').val();

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }
   if(test_name === '0'){
      uzp.showNotification('Please select the test.', 'error');
      return;
   }
   if(test_result === '0'){
      uzp.showNotification('Please select the test result.', 'error');
      return;
   }
   
   //check if all the tests selected
   var testResults = [];
   var radioResIndex = {};
   var allGood = true;
   $("input[type='radio']").each(function(){
      var selectId = $(this).attr('name');
      var idRegex = /res[0-9]_select/;
      if(idRegex.test(selectId) == true) {
         //check if select is visible
         var idParts = selectId.split("_");
         if(idParts.length == 2) {
            var testName = $("#"+idParts[0]+"_label").html();
            var testIndex = -1;
            if(typeof radioResIndex[selectId] == 'undefined') {
               testIndex = testResults.length;
               radioResIndex[selectId] = testIndex;
            }
            else {
               testIndex = radioResIndex[selectId];
            }
            if($("input[name="+selectId+"]:checked").val().length > 0){
               testResults[testIndex] = {name:testName, result:$("input[name="+selectId+"]:checked").val()};
            }
            else {
               uzp.showNotification('Please select a vaule for '+testName, 'error');
               $(this).focus();
               allGood = false;
               return;
            }
         }
      }
   });
   if(allGood == false) return;
   console.log(testResults);
   if($("#testId").val().length == 0) {
      uzp.showNotification('Please select a test', 'error');
      $("#testId").focus();
   }
   // seems all is well, lets save the sample
   $.ajax({
      type:"POST", url: "mod_ajax.php?page=step7&do=save", async: false, dataType:'json', data: {cur_user: cur_user, sample: sample, test: $("#testId").val(), observations: testResults},
      success: function (data) {
         if(data.error === true){
            uzp.showNotification(data.mssg, 'error');
            $("[name=sample]").focus().val('');
            return;
         }
         else{
            // we have saved the sample well... lets prepare for the next sample
            $("[name=sample]").focus().val('');
            var currentdate = new Date();
            var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
            $('.received .saved').prepend(datetime +': '+ sample +'=>'+ test_name +"<br />");
            //reset test and result
            $('#testId').val("0");
            $('#testResultId').val("0");
         }
     }
  });
};

Uzp.prototype.saveAstResult = function(){
   // get the sample format and the received sample
   var sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), drug = $('#drugNameId').val(), drug_value = $('[name=drug_value]').val();

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }
   if(drug === '0'){
      uzp.showNotification('Please select the drug.', 'error');
      return;
   }
   if(drug_value === ''){
      uzp.showNotification('Please select the test result.', 'error');
      return;
   }
   
   //check to see if all the drugs are entered
   var drugs = [];
   var drugIdRegex = /^drug_.+_val[0-9]{1}$/;
   var allGood = true;
   $("input[type=text]").each(function(){
      var inputId = $(this).attr('id');
      if(drugIdRegex.test(inputId) == true) {
         console.log("input with id = ",inputId," fits the regex");
         var inputParts = inputId.split("_");
         if(inputParts.length == 3) {//we only expect 3 parts eg [drug]_[ASV1]_[val1]
            if(typeof drugs[inputParts[1]] == "undefined") {
               drugs[inputParts[1]] = {val1:undefined,val2:undefined};
            }
            if($(this).val().length == 0) {
               uzp.showNotification("Please fill the value of "+inputParts[1], "error");
               $(this).focus();
               allGood = false;
               return;
            }
            else {
               drugs[inputParts[1]][inputParts[2]] = $(this).val();
            }
         }
         else {
            console.log("Drug fits regex but really doesn't have 3 parts", inputId);
            allGood = false;
            return;
         }
      }
   });
   if(allGood == false) return;
   var cleanDrugs = [];
   var drugKeys = Object.keys(drugs);
   console.log(drugKeys);
   console.log("Length of drugs array = ",drugs.length);
   for(var index = 0; index < drugKeys.length; index++) {
      console.log("Currently at drug ", drugs[drugKeys[index]]);
      if(typeof drugs[drugKeys[index]].val1 == 'undefined'
              || typeof drugs[drugKeys[index]].val2 == 'undefined') {
         uzp.showNotification("Please fill the value of "+drugKeys[index], "error");
         $("#drug_"+drugs[drugKeys[index]]+"_val1").focus();
         allGood = false;
         return;
      }
      else if(drugs[drugKeys[index]].val1 != drugs[drugKeys[index]].val2){
         uzp.showNotification("The first and second values of "+drugKeys[index]+" do not match", "error");
         $("#drug_"+drugs[drugKeys[index]]+"_val1").focus();
         allGood = false;
         return;
      }
      else {//value probably fine, add to clean drugs list
         cleanDrugs[cleanDrugs.length] = {name:drugKeys[index], value:drugs[drugKeys[index]].val1};
      }
   }
   if(allGood == false) return;
   console.log(cleanDrugs);
   // seems all is well, lets save the sample
   $.ajax({
      //array('plate45_id' => $result[0]['id'], 'drug' => $_POST['drug'], 'value' => $_POST['drug_value'], 'user' => $_POST['cur_user'])
      type:"POST", url: "mod_ajax.php?page=step10&do=save", async: false, dataType:'json', data: {cur_user: cur_user, sample: sample, drugs: cleanDrugs},
      success: function (data) {
         if(data.error === true){
            uzp.showNotification(data.mssg, 'error');
            $(":input").val('');
            $("[name=sample]").focus().val('');
            return;
         }
         else{
            // we have saved the sample well... lets prepare for the next sample
            $("[name=sample]").focus().val('');
            var currentdate = new Date();
            var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
            $('.received .saved').prepend(datetime +': '+ sample +'=>'+ cleanDrugs.length +" drugs<br />");
            //reset test and result
            $('#testId').val("0");
            $('#testResultId').val("0");
         }
     }
  });
};

Uzp.prototype.savePlateToEppendorfs = function(){
   // get the sample format and the received sample
   var sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), no_eppendorfs = $('[name=no_eppendorfs]').val();

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }

   // seems all is well, lets save the sample
   $.ajax({
      //array('plate45_id' => $result[0]['id'], 'drug' => $_POST['drug'], 'value' => $_POST['drug_value'], 'user' => $_POST['cur_user'])
      type:"POST", url: "mod_ajax.php?page=step12&do=save", async: false, dataType:'json', data: {cur_user: cur_user, sample: sample},
      success: function (data) {
         if(data.error === true){
            uzp.showNotification(data.mssg, 'error');
            $("[name=sample]").focus().val('');
            $("#eppendorf_label").html("");
            return;
         }
         else{
            $("#eppendorf_label").html(sample + " eppendorf label => " + data.eppendorf);
            // we have saved the sample well... lets prepare for the next sample
            $("[name=sample]").focus().val('');
            var currentdate = new Date();
            var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
            $('.received .saved').prepend(datetime +': '+ sample +'=>'+ data.eppendorf +"<br />");
         }
     }
  });
};

Uzp.prototype.addMediumSample = function(sample) {
   //check if the sample aleady exists in the list
   var found = false;
   for(var index = 0; index < uzp.mediumBarcodeList.length; index++) {
      if(uzp.mediumBarcodeList[index] == sample) {
         found = true;
         break;
      }
   }
   if(found == false) {
      uzp.mediumBarcodeList[uzp.mediumBarcodeList.length] = sample;
      $("#scanned_colonies").append("<div>"+sample+"</div>");
   }
};
Uzp.prototype.resetMediumSampleList = function() {
   uzp.mediumBarcodeList = [];
   uzp.plateSample = undefined;
   $("#scanned_colonies").html("");
};

/**
 * Saves the current association of plates and colonies
 * @returns {undefined}    Returns nothing
 */
Uzp.prototype.saveColonies = function(){
   // check for the pre-requisites
   var colonies_format = $('[name=colonies_format]').val(), plate_format = $('[name=plate_format]').val();
   var sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), curSampleType = undefined;

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(plate_format === '' || plate_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the broth. It should be something like \'BSR010959\'', 'error');
      $("[name=broth_format]").focus();
      return;
   }
   if(colonies_format === '' || colonies_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the plate barcode. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=plate_format]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      $("[name=sample]").focus().val('');
      return;
   }

   //lets validate the samples
   var p_regex = uzp.createSampleRegex(plate_format);
   var c_regex = uzp.createSampleRegex(colonies_format);

   // check whether we are dealing with the field or broth sample
   if(p_regex.test(sample) === true){
      if(uzp.parentSample !== undefined && uzp.colonies.length !== 0){
         // lets save this association
         $.ajax({
            type:"POST", url: "mod_ajax.php?page=step4&do=save", async: false, dataType:'json', data: {plate: uzp.parentSample, colonies: uzp.colonies, cur_user: cur_user},
            success: function (data) {
               if(data.error === true){
                  uzp.showNotification(data.mssg, 'error');
                  $("[name=sample]").focus().val('');
                  $('#label_scanned_colonies').html('Scanned colonies');
                  uzp.parentSample = undefined; uzp.curSample = undefined; uzp.colonies = [];
                  $('#scanned_colonies').html('');
                  return;
               }
               else{
                  // we have saved the sample well...
                  $("[name=sample]").focus().val('');
                  var currentdate = new Date();
                  var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
                  $.each(uzp.colonies, function(i, that){
                     $('.received .saved').prepend(datetime +': '+ uzp.parentSample +'=>'+ that +"<br />");
                  });

                  // lets prepare for the next sample
                  uzp.parentSample = sample; uzp.curSample = undefined; uzp.colonies = [];
                  $('#scanned_colonies').html('');
                  $('#label_scanned_colonies').html('Scanned colonies for <b>'+ sample +'</b>');
                  return;
               }
           }
        });
      }
      else{
         uzp.parentSample = sample;
         $('#label_scanned_colonies').html('Scanned colonies for <b>'+ sample +'</b>');
         // we have a parent plate
         uzp.showNotification('Got a plate with barcode... ' +uzp.parentSample+ ' scan the colonies.', 'mail');
         $("[name=sample]").focus().val('');
         uzp.colonies = [];
         return;
      }
   }
   else if(c_regex.test(sample) === true){
      if(uzp.parentSample !== undefined){
         // add the sample to the list of colonies and to the div
         uzp.colonies[uzp.colonies.length] = sample;
         var currentdate = new Date();
         var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
         $('#scanned_colonies').prepend(datetime +': '+ sample +"<br />");

         // we have a colony
         uzp.showNotification('Colony with barcode... '+ sample +'....', 'mail');
         $("[name=sample]").focus().val('');
         return;
      }
      else{
         uzp.showNotification('You can\'t scan a colony without scanning the plate first. Current barcode'+ sample +'!', 'error');
         return;
      }
   }
   else{
      // we don't know the sample format...so reject it and invalidate all the other settings
      uzp.showNotification('Error! Unknown format for the entered sample.'+sample, 'error');
      $("[name=sample]").focus().val('');
      uzp.parentSample = undefined; uzp.curSample = undefined; uzp.colonies = [];
      $('#scanned_colonies').html('');
      return;
   }
};

/**
 * Saves the current association of plates and colonies
 * @returns {undefined}    Returns nothing
 */
Uzp.prototype.saveBioChemPrep = function(){
   // check for the pre-requisites
   var plate_format = $('[name=plate_format]').val(), media_format = $('[name=media_format]').val();
   var sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), curSampleType = undefined;

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(media_format === '' || media_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the broth. It should be something like \'BSR010959\'', 'error');
      $("[name=broth_format]").focus();
      return;
   }
   if(plate_format === '' || plate_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the plate barcode. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=media_format]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      $("[name=sample]").focus().val('');
      return;
   }

   //lets validate the samples
   var p_regex = uzp.createSampleRegex(plate_format);
   var c_regex = uzp.createSampleRegex(media_format);

   // check whether we are dealing with the field or broth sample
   if(p_regex.test(sample) === true){
      if(uzp.parentSample !== undefined && uzp.colonies.length !== 0){
         // lets save this association
         $.ajax({
            type:"POST", url: "mod_ajax.php?page=step6&do=save", async: false, dataType:'json', data: {plate: uzp.parentSample, colonies: uzp.colonies, cur_user: cur_user},
            success: function (data) {
               if(data.error === true){
                  uzp.showNotification(data.mssg, 'error');
                  $("[name=sample]").focus().val('');
                  $('#label_scanned_colonies').html('Scanned colonies');
                  uzp.parentSample = undefined; uzp.curSample = undefined; uzp.colonies = [];
                  $('#scanned_colonies').html('');
                  return;
               }
               else{
                  // we have saved the sample well...
                  $("[name=sample]").focus().val('');
                  var currentdate = new Date();
                  var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
                  $.each(uzp.colonies, function(i, that){
                     $('.received .saved').prepend(datetime +': '+ uzp.parentSample +'=>'+ that +"<br />");
                  });

                  // lets prepare for the next sample
                  uzp.parentSample = sample; uzp.curSample = undefined; uzp.colonies = [];
                  $('#scanned_colonies').html('');
                  $('#label_scanned_colonies').html('Scanned colonies for <b>'+ sample +'</b>');
                  return;
               }
           }
        });
      }
      else{
         uzp.parentSample = sample;
         $('#label_scanned_colonies').html('Scanned colonies for <b>'+ sample +'</b>');
         // we have a parent plate
         uzp.showNotification('Got a plate with barcode... ' +uzp.parentSample+ ' scan the colonies.', 'mail');
         $("[name=sample]").focus().val('');
         uzp.colonies = [];
         return;
      }
   }
   else if(c_regex.test(sample) === true){
      if(uzp.parentSample !== undefined){
         // add the sample to the list of colonies and to the div
         uzp.colonies[uzp.colonies.length] = sample;
         var currentdate = new Date();
         var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
         $('#scanned_colonies').prepend(datetime +': '+ sample +"<br />");

         // we have a colony
         uzp.showNotification('Colony with barcode... '+ sample +'....', 'mail');
         $("[name=sample]").focus().val('');
         return;
      }
      else{
         uzp.showNotification('You can\'t scan a colony without scanning the plate first. Current barcode'+ sample +'!', 'error');
         return;
      }
   }
   else{
      // we don't know the sample format...so reject it and invalidate all the other settings
      uzp.showNotification('Error! Unknown format for the entered sample.'+sample, 'error');
      $("[name=sample]").focus().val('');
      uzp.parentSample = undefined; uzp.curSample = undefined; uzp.colonies = [];
      $('#scanned_colonies').html('');
      return;
   }
};

Uzp.prototype.savePlate3to45 = function(){
   // check for the pre-requisites
   var plate_format = $('[name=plate_format]').val(), media_format = $('[name=media_format]').val();
   var sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), curSampleType = undefined;

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(media_format === '' || media_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the broth. It should be something like \'BSR010959\'', 'error');
      $("[name=broth_format]").focus();
      return;
   }
   if(plate_format === '' || plate_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the plate barcode. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=media_format]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      $("[name=sample]").focus().val('');
      return;
   }

   //lets validate the samples
   var p_regex = uzp.createSampleRegex(plate_format);
   var c_regex = uzp.createSampleRegex(media_format);

   // check whether we are dealing with the field or broth sample
   if(p_regex.test(sample) === true){
      if(uzp.parentSample !== undefined && uzp.colonies.length !== 0){
         // lets save this association
         $.ajax({
            type:"POST", url: "mod_ajax.php?page=step9&do=save", async: false, dataType:'json', data: {plate: uzp.parentSample, colonies: uzp.colonies, cur_user: cur_user},
            success: function (data) {
               if(data.error === true){
                  uzp.showNotification(data.mssg, 'error');
                  $("[name=sample]").focus().val('');
                  $('#label_scanned_colonies').html('Scanned colonies');
                  uzp.parentSample = undefined; uzp.curSample = undefined; uzp.colonies = [];
                  $('#scanned_colonies').html('');
                  return;
               }
               else{
                  // we have saved the sample well...
                  $("[name=sample]").focus().val('');
                  var currentdate = new Date();
                  var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
                  $.each(uzp.colonies, function(i, that){
                     $('.received .saved').prepend(datetime +': '+ uzp.parentSample +'=>'+ that +"<br />");
                  });

                  // lets prepare for the next sample
                  uzp.parentSample = sample; uzp.curSample = undefined; uzp.colonies = [];
                  $('#scanned_colonies').html('');
                  $('#label_scanned_colonies').html('Scanned colonies for <b>'+ sample +'</b>');
                  return;
               }
           }
        });
      }
      else{
         uzp.parentSample = sample;
         $('#label_scanned_colonies').html('Scanned colonies for <b>'+ sample +'</b>');
         // we have a parent plate
         uzp.showNotification('Got a plate with barcode... ' +uzp.parentSample+ ' scan the colonies.', 'mail');
         $("[name=sample]").focus().val('');
         uzp.colonies = [];
         return;
      }
   }
   else if(c_regex.test(sample) === true){
      if(uzp.parentSample !== undefined){
         // add the sample to the list of colonies and to the div
         uzp.colonies[uzp.colonies.length] = sample;
         var currentdate = new Date();
         var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
         $('#scanned_colonies').prepend(datetime +': '+ sample +"<br />");

         // we have a colony
         uzp.showNotification('Colony with barcode... '+ sample +'....', 'mail');
         $("[name=sample]").focus().val('');
         return;
      }
      else{
         uzp.showNotification('You can\'t scan a colony without scanning the plate first. Current barcode'+ sample +'!', 'error');
         return;
      }
   }
   else{
      // we don't know the sample format...so reject it and invalidate all the other settings
      uzp.showNotification('Error! Unknown format for the entered sample.'+sample, 'error');
      $("[name=sample]").focus().val('');
      uzp.parentSample = undefined; uzp.curSample = undefined; uzp.colonies = [];
      $('#scanned_colonies').html('');
      return;
   }
};

Uzp.prototype.saveColoniesPositions = function(){
   // get the sample format and the received sample
   var colonies_format = $('[name=colonies_format]').val(), storage_box = $('[name=storage_box]').val(), sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), cur_pos = $('[name=colony_pos]').val();

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(colonies_format === '' || colonies_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the colonies. It should be something like \'BSR010959\'', 'error');
      $("[name=colonies_format]").focus();
      return;
   }
   if(storage_box === '' || storage_box === undefined){
      uzp.showNotification('Please scan the barcode for the storage boxes. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=storage_box]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }
   if(cur_pos === ''){
      uzp.showNotification('Please enter the current position of the colony.', 'error');
      return;
   }

   //lets validate the aliquot format
   var c_regex = uzp.createSampleRegex(colonies_format);
   var b_regex = uzp.createSampleRegex(storage_box);

   // check whether we are dealing with the field or broth sample
   if(c_regex.test(sample) === true){
      // save the colony to the next slot of this box
      $.ajax({
         type:"POST", url: "mod_ajax.php?page=step5.1&do=save", async: false, dataType:'json', data: {colony: sample, box: storage_box, cur_user: cur_user, cur_pos: cur_pos},
         success: function (data) {
            if(data.error === true){
               uzp.showNotification(data.mssg, 'error');
               $("[name=sample]").focus().val('');
               return;
            }
            else{
               // we have saved the sample well... lets prepare for the next sample
               $("[name=sample]").focus().val('');
               var suffix = sample.match(/([0-9]+)$/i);
               $('#plate_layout .pos_'+cur_pos).html(suffix[0] +' ('+ cur_pos +')').css({'background-color': '#009D59'});
               $('[name=colony_pos]').val(parseInt(cur_pos)+1);
               uzp.showNotification(data.mssg, 'success');
            }
         }
      });
   }
   else{
      // we don't know the sample format...so reject it and invalidate all the other settings
      uzp.showNotification('Error! Unknown format for the entered sample.'+sample, 'error');
      $("[name=sample]").focus().val('');
      return;
   }
};

Uzp.prototype.saveRegrow = function(){
   // get the sample format and the received sample
   var colony_format = $('[name=colony_format]').val(), plate_format = $('[name=plate_format]').val(), sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), curSampleType = undefined;

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(plate_format === '' || plate_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the plate. It should be something like \'BSR010959\'', 'error');
      $("[name=colony_format]").focus();
      return;
   }
   if(colony_format === '' || colony_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the archived sample. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=colony_format]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }

   //lets validate the aliquot format
   var s_regex = uzp.createSampleRegex(colony_format);
   var b_regex = uzp.createSampleRegex(plate_format);

   // check whether we are dealing with the field or broth sample
   if(s_regex.test(sample) === true){
      // we have a field sample
      curSampleType = 'field_sample';
   }
   else if(b_regex.test(sample) === true){
      // we have a broth sample
      curSampleType = 'broth_sample';
   }
   else{
      // we don't know the sample format...so reject it and invalidate all the other settings
      uzp.showNotification('Error! Unknown format for the entered sample.'+sample, 'error');
      $("[name=sample]").focus().val('');
      uzp.prevSampleType = undefined; uzp.curSampleType = undefined;
      uzp.prevSample = undefined; uzp.curSample = undefined;
      return;
   }

   uzp.prevSampleType = uzp.curSampleType;
   uzp.curSampleType = curSampleType;
   uzp.prevSample = uzp.curSample;  // move the previous current sample to the previous sample
   uzp.curSample = sample;

   var res = uzp.validateScannedSamples({firstSample: 'field_sample', secondSample: 'broth_sample'});
   if(res === 1){ return; }

   // seems all is well, lets save the sample
   $.ajax({
      type:"POST", url: "mod_ajax.php?page=step11&do=save", async: false, dataType:'json', data: {plate_format: b_regex, colony_format: s_regex, field_sample: uzp.prevSample, broth_sample: uzp.curSample, cur_user: cur_user},
      success: function (data) {
         if(data.error === true){
            uzp.showNotification(data.mssg, 'error');
            $("[name=sample]").focus().val('');
            return;
         }
         else{
            // we have saved the sample well... lets prepare for the next sample
            $("[name=sample]").focus().val('');
            var currentdate = new Date();
            var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
            $('.received .saved').prepend(datetime +': '+ uzp.prevSample +'=>'+uzp.curSample +"<br />");
         }
     }
  });
};

Uzp.prototype.saveDnaArchiving = function(){
   // get the sample format and the received sample
   var colony_format = $('[name=colony_format]').val(), plate_format = $('[name=plate_format]').val(), sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), curSampleType = undefined;

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(plate_format === '' || plate_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the plate. It should be something like \'BSR010959\'', 'error');
      $("[name=colony_format]").focus();
      return;
   }
   if(colony_format === '' || colony_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the archived sample. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=colony_format]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }

   //lets validate the aliquot format
   var s_regex = uzp.createSampleRegex(colony_format);
   var b_regex = uzp.createSampleRegex(plate_format);

   // check whether we are dealing with the field or broth sample
   if(s_regex.test(sample) === true){
      // we have a field sample
      curSampleType = 'field_sample';
   }
   else if(b_regex.test(sample) === true){
      // we have a broth sample
      curSampleType = 'broth_sample';
   }
   else{
      // we don't know the sample format...so reject it and invalidate all the other settings
      uzp.showNotification('Error! Unknown format for the entered sample.'+sample, 'error');
      $("[name=sample]").focus().val('');
      uzp.prevSampleType = undefined; uzp.curSampleType = undefined;
      uzp.prevSample = undefined; uzp.curSample = undefined;
      return;
   }

   uzp.prevSampleType = uzp.curSampleType;
   uzp.curSampleType = curSampleType;
   uzp.prevSample = uzp.curSample;  // move the previous current sample to the previous sample
   uzp.curSample = sample;

   var res = uzp.validateScannedSamples({firstSample: 'field_sample', secondSample: 'broth_sample'});
   if(res === 1){ return; }

   // seems all is well, lets save the sample
   $.ajax({
      type:"POST", url: "mod_ajax.php?page=step13&do=save", async: false, dataType:'json', data: {plate_format: b_regex, colony_format: s_regex, eppendorf: uzp.prevSample, dna: uzp.curSample, cur_user: cur_user},
      success: function (data) {
         if(data.error === true){
            uzp.showNotification(data.mssg, 'error');
            $("[name=sample]").focus().val('');
            return;
         }
         else{
            // we have saved the sample well... lets prepare for the next sample
            $("[name=sample]").focus().val('');
            var currentdate = new Date();
            var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
            $('.received .saved').prepend(datetime +': '+ uzp.prevSample +'=>'+uzp.curSample +"<br />");
         }
     }
  });
};

Uzp.prototype.savePlate3 = function(){
   // get the sample format and the received sample
   var colony_format = $('[name=colony_format]').val(), plate_format = $('[name=plate_format]').val(), sample = $('[name=sample]').val().toUpperCase(), cur_user = $('#usersId').val(), curSampleType = undefined;

   if(sample === ''){
      uzp.showNotification('Please scan/enter the sample to save.', 'error');
      $("[name=sample]").focus();
      return;
   }
   if(plate_format === '' || plate_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the plate. It should be something like \'BSR010959\'', 'error');
      $("[name=colony_format]").focus();
      return;
   }
   if(colony_format === '' || colony_format === undefined){
      uzp.showNotification('Please scan a sample barcode for the archived sample. It should be something like \'AVAQ70919\'.', 'error');
      $("[name=colony_format]").focus();
      return;
   }
   if(cur_user === '0'){
      uzp.showNotification('Please select the current user.', 'error');
      return;
   }

   //lets validate the aliquot format
   var s_regex = uzp.createSampleRegex(colony_format);
   var b_regex = uzp.createSampleRegex(plate_format);

   // check whether we are dealing with the field or broth sample
   if(s_regex.test(sample) === true){
      // we have a field sample
      curSampleType = 'field_sample';
   }
   else if(b_regex.test(sample) === true){
      // we have a broth sample
      curSampleType = 'broth_sample';
   }
   else{
      // we don't know the sample format...so reject it and invalidate all the other settings
      uzp.showNotification('Error! Unknown format for the entered sample.'+sample, 'error');
      $("[name=sample]").focus().val('');
      uzp.prevSampleType = undefined; uzp.curSampleType = undefined;
      uzp.prevSample = undefined; uzp.curSample = undefined;
      return;
   }

   uzp.prevSampleType = uzp.curSampleType;
   uzp.curSampleType = curSampleType;
   uzp.prevSample = uzp.curSample;  // move the previous current sample to the previous sample
   uzp.curSample = sample;

   var res = uzp.validateScannedSamples({firstSample: 'field_sample', secondSample: 'broth_sample'});
   if(res === 1){ return; }

   // seems all is well, lets save the sample
   $.ajax({
      type:"POST", url: "mod_ajax.php?page=step8&do=save", async: false, dataType:'json', data: {plate_format: b_regex, colony_format: s_regex, field_sample: uzp.prevSample, broth_sample: uzp.curSample, cur_user: cur_user},
      success: function (data) {
         if(data.error === true){
            uzp.showNotification(data.mssg, 'error');
            $("[name=sample]").focus().val('');
            return;
         }
         else{
            // we have saved the sample well... lets prepare for the next sample
            $("[name=sample]").focus().val('');
            var currentdate = new Date();
            var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + ":" + currentdate.getSeconds();
            $('.received .saved').prepend(datetime +': '+ uzp.prevSample +'=>'+uzp.curSample +"<br />");
         }
     }
  });
};