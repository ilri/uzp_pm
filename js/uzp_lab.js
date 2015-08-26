/**
 * The constructor of the Uzp functionality
 *
 * @returns {Uzp}
 */
function Uzp(currUri, currHtml, lastInputId) {
   window.uzp_lab = this;

   // initialize the main variables
   window.uzp_lab.sub_module = Common.getVariable('do', document.location.search.substring(1));
   window.uzp_lab.module = Common.getVariable('page', document.location.search.substring(1));

   this.serverURL = "./modules/mod_uzp_lab_general.php";
   this.procFormOnServerURL = "mod_ajax.php";

   // create the notification place
   this.prevNotificationClass = 'info';
   this.currUri = currUri;
   this.currHtml = currHtml;
   this.lastInputId = lastInputId;
   this.prevUri = null;
   this.nextUri = null;
   this.animalId = null;
   
   $("#content_container").html(currHtml);
   this.inputTypes = "input,select,textarea";
   this.inputSuffix = "_input";
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
                  if ((i + 1) < allInputs.length)
                     $(allInputs[i + 1]).focus();
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

Uzp.prototype.goToNextPage = function(animalId) {
   if(window.uzp_lab.nextUri != null) window.location.href = "?page=pm&do="+window.uzp_lab.nextUri+"&animal="+animalId;
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
      if(currInput.attr('id').indexOf(window.uzp_lab.inputSuffix) > 0) {//the input id contains the input suffix
         var id = currInput.attr('id').substr(0, (currInput.attr('id').indexOf(window.uzp_lab.inputSuffix)));
         values[id] = currInput.val();
      }
   }
   console.log(values);
   return values;
};

/**
 * 
 * @returns {undefined}
 */
Uzp.prototype.commit = function(direction) {
   var inputValues = window.uzp_lab.getInputValues();
   $.ajax({
      type:"POST", url: "mod_ajax.php?page=pm&do=commit&curr_step="+window.uzp_lab.currUri+"&animal="+window.uzp_lab.animalId, async: false, dataType:'json', data: inputValues,
      success: function (data) {
         console.log(data);
         if(data.error === true){
            window.uzp_lab.showNotification(data.message, 'error');
            return;
         }
         else{
            if(direction == "previous") window.uzp_lab.goToPreviousPage(data.animal);
            else window.uzp_lab.goToNextPage(data.animal);
         }
     }
  });
};
