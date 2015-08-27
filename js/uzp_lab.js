/**
 * The constructor of the Uzp functionality
 *
 * @returns {Uzp}
 */
function Uzp(currUri, lastInputId) {
   window.uzp_lab = this;

   // initialize the main variables
   window.uzp_lab.sub_module = Common.getVariable('do', document.location.search.substring(1));
   window.uzp_lab.module = Common.getVariable('page', document.location.search.substring(1));

   this.serverURL = "./modules/mod_uzp_lab_general.php";
   this.procFormOnServerURL = "mod_ajax.php";

   // create the notification place
   this.prevNotificationClass = 'info';
   this.currUri = currUri;
   this.lastInputId = lastInputId;
   this.prevUri = null;
   this.nextUri = null;
   this.animalId = null;
   this.rules = {};
   
   this.inputTypes = "input,select,textarea";
   this.inputSuffix = "_input";
   this.uploaderSuffix = "_uploader";
   //bind key presses
   $(":input").bind("keydown", function(e) {
      if (e.keyCode == 13) {
         var allInputs = $(window.uzp_lab.inputTypes);
         console.log(allInputs);
         for (var i = 0; i < allInputs.length; i++) {
            if (allInputs[i] == this) {
               console.log($(allInputs[i]).attr('id'));
               console.log(window.uzp_lab.lastInputId);
               if($(allInputs[i]).attr('id') == window.uzp_lab.lastInputId){
                  window.uzp_lab.commit('next');
               }
               else {
                  while ((allInputs[i]).name == (allInputs[i + 1]).name) {
                     i++;
                  }
                  if ((i + 1) < allInputs.length){
                     //check for the next focusable input
                     var nextInput = i + 1;
                     while(nextInput < allInputs.length) {
                        if($(allInputs[nextInput]).is(":disabled")){
                           if(nextInput == (allInputs.length - 1)) {//last input
                              window.uzp_lab.commit('next');
                           }
                           else {
                              nextInput++;
                           }
                        }
                        else {
                           $($(allInputs[nextInput])).focus();
                           break;
                        }
                     }
                  }
               }
            }
         }
      }
   });
   
   $(document).ready(function(){
      $("#notification_box").jqxNotification({ width: 250, position: "top-right", opacity: 0.9, autoOpen: false, animationOpenDelay: 800, autoClose: true, autoCloseDelay: 3000, template: "info", showCloseButton: false });
      //set click listeners for next and previous divs
      $("#next_step").click(function(){
         console.log("click");
         window.uzp_lab.commit('next');
      });
      $("#prev_step").click(function(){
         window.uzp_lab.commit('previous');
      });
   });
};

Uzp.prototype.registerUploader = function(inputId) {
   var uploaderId = inputId + window.uzp_lab.uploaderSuffix;
   $("#"+uploaderId).jqxFileUpload({
      width:400,
      fileInputName: "data_file",
      uploadUrl: "mod_ajax.php?page=pm&do=upload",
      autoUpload: true
   });
   
   //register an on pre and post upload 
   $("#"+uploaderId).on("uploadStart", function(){
      //reset the name stored in the hidden input
      $("#"+inputId+window.uzp_lab.inputSuffix).val("");
   });
   $("#"+uploaderId).on("uploadEnd", window.uzp_lab.onUploadEnd);
};

Uzp.prototype.onUploadEnd = function(event) {
   var inputId = $(event.target).attr("id").substr(0, $(event.target).attr("id").indexOf(window.uzp_lab.uploaderSuffix));
   var serverResponse = event.args.response;
   var jsonResponse = $.parseJSON(serverResponse);
   if(jsonResponse.error == false) {
      var fileName = jsonResponse.fileName;
      $("#"+inputId+window.uzp_lab.inputSuffix).val(fileName);
      window.uzp_lab.showNotification("Successfully uploaded "+inputId+" file", 'info');
   }
   else {
      window.uzp_lab.showNotification("Could not upload file for "+inputId, 'error');
   }
};

Uzp.prototype.goToNextPage = function(animalId) {
   if(window.uzp_lab.nextUri != null) window.location.href = "?page=pm&do="+window.uzp_lab.nextUri+"&animal="+animalId;
   else window.location.href = "?page=pm";
};

Uzp.prototype.goToPreviousPage = function(animalId) {
   if(window.uzp_lab.prevUri != null) window.location.href = "?page=pm&do="+window.uzp_lab.prevUri+"&animal="+animalId;
};

Uzp.prototype.setAnimalId = function(animalId) {
   if(!isNaN(animalId)){
      window.uzp_lab.animalId = animalId;
   }
   else {
      window.uzp_lab.animalId = null;
      console.log("Animal id is not a number");
   }
};

Uzp.prototype.setPrevStep = function(prevStepUri) {
   window.uzp_lab.prevUri = prevStepUri;
};

Uzp.prototype.setNextStep = function(nextStepUri) {
   window.uzp_lab.nextUri = nextStepUri;
};

Uzp.prototype.setDependsOn = function(inputId, dependsOnId, possibleValues) {
   dependsOnId = dependsOnId + window.uzp_lab.inputSuffix;
   inputId = inputId + window.uzp_lab.inputSuffix;
   console.log(dependsOnId);
   $("#"+dependsOnId).change(function(){
      console.log("depends on changed");
      if(possibleValues.indexOf($("#"+dependsOnId).val()) >= 0) {//value has changed to one we want
         $("#"+inputId).removeAttr("disabled");
      }
      else {
         $("#"+inputId).attr("disabled", "disabled");
      }
   });
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

Uzp.prototype.addRule = function(inputId, ruleType, data) {
   if(typeof window.uzp_lab.rules[inputId] == 'undefined'){
      window.uzp_lab.rules[inputId] = {};
   }
   if(ruleType == 'regex') {//make sure the value of the input meets the specifed regex
      console.log("Adding regex rule for "+inputId);
      var regex = window.uzp_lab.createSampleRegex(data);
      window.uzp_lab.rules[inputId]['regex'] = regex;
   }
   else if(ruleType == 'bounds') {
      console.log("Adding bounds to "+inputId);
      window.uzp_lab.rules[inputId]['bounds'] = data;
   }
};

/**
 * This function checks for all the input types and returns an array with their values.
 * The function also removes any input name prefix/suffix
 * 
 * @returns {Array} An array containing the inputs with ids as array keys and values as array values
 */
Uzp.prototype.getInputValues = function (){
   var values = {};
   var allInputs = $(window.uzp_lab.inputTypes);
   for(var index = 0; index < allInputs.length; index++) {
      var currInput = $(allInputs[index]);
      if(typeof currInput.attr('id') != 'undefined' && currInput.attr('id').indexOf(window.uzp_lab.inputSuffix) > 0) {//the input id contains the input suffix
         var id = currInput.attr('id').substr(0, (currInput.attr('id').indexOf(window.uzp_lab.inputSuffix)));
         values[id] = currInput.val();
         if(values[id] == '') values[id] = null;
      }
   }
   return values;
};

Uzp.prototype.validateValues = function(data) {
   var inputIds = Object.keys(data);
   var response = {error:false, message:''};
   for(var index = 0; index < inputIds.length; index++) {
      var currInputId = inputIds[index];
      if(typeof window.uzp_lab.rules[currInputId] != 'undefined') {
         var currRules = window.uzp_lab.rules[currInputId];
         if(typeof currRules['regex'] != 'undefined') {
            //check if the data meets the regex
            if(typeof data[currInputId] != 'undefined' && data[currInputId] != null && data[currInputId].length > 0 && currRules['regex'].test(data[currInputId]) === false) {
               response = {error:true, message:currInputId+' does not follow the required format'};
               $("#"+currInputId+window.uzp_lab.inputSuffix).focus();
               return response;
            }
         }
         if(typeof currRules['bounds'] != 'undefined') {
            if(typeof data[currInputId] != 'undefined' && data[currInputId] != null && $.isNumeric(data[currInputId])) {
               if(data[currInputId] < currRules['bounds'].min) {
                  response = {error:true, message:currInputId+' is less than '+currRules['bounds'].min};
                  $("#"+currInputId+window.uzp_lab.inputSuffix).focus();
                  return response;
               }
               else if(data[currInputId] > currRules['bounds'].max) {
                  response = {error:true, message:currInputId+' is greater than '+currRules['bounds'].max};
                  $("#"+currInputId+window.uzp_lab.inputSuffix).focus();
                  return response;
               }
            }
         }
      }
   }
   return response;
};

/**
 * 
 * @returns {undefined}
 */
Uzp.prototype.commit = function(direction) {
   var inputValues = window.uzp_lab.getInputValues();
   var validation = window.uzp_lab.validateValues(inputValues);
   if(validation.error == false) {
      $.ajax({
         type:"POST", url: "mod_ajax.php?page=pm&do=commit&curr_step="+window.uzp_lab.currUri+"&animal="+window.uzp_lab.animalId+"&direction="+direction, async: false, dataType:'json', data: inputValues,
         success: function (data) {
            console.log(data);
            if(data.error === true){
               window.uzp_lab.showNotification(data.message, 'error');
               if(typeof data.focus != 'undefined') {
                  var inputId = data.focus + window.uzp_lab.inputSuffix;
                  console.log("Focusing on "+inputId);
                  $("#"+inputId).focus();
               }
               return;
            }
            else{
               if(direction == "previous") window.uzp_lab.goToPreviousPage(data.animal);
               else window.uzp_lab.goToNextPage(data.animal);
            }
        }
     });
   }
   else {
      window.uzp_lab.showNotification(validation.message, 'error');
   }
};
