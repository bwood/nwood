/* techcommons - jquery-custom.js (Drupal 6) - 7/12/11 */

/* Override default Node Relationships buttons in node_form.js */
Drupal.theme.nodeRelationshipsReferenceButton = function(type, title) {
  var refButtonText = ''; //In order to use text instead of button images

  if (type == 'search') {
    title = 'Select an Existing Support Team';
    refButtonText = 'Select an Existing Support Team';
  }
  else if (type == 'create') {
    title = 'Create a Support Team';
    refButtonText = 'Create a Support Team';    
  }
  else if (type == 'edit') {
    title = 'Edit a Support Team';
    refButtonText = 'Edit a Support Team';
  }
  return '<a href="javascript:void(0)" class="noderelationships-nodereference-'+ type +'-button" title="'+ title +'">' + refButtonText + '</a>';
};


$(document).ready(function(){
   
   /* Override title attribute for .noderelationships-nodereference-edit-disabled */
   $('.noderelationships-nodereference-edit-disabled').attr("title", "Edit Support Team [disabled]");
 
   /* Use these classes for custom collapse/expand functionality */    
   $('.jquery-body').hide();   
   $('.jquery-collapse').hide();
   $('.jquery-expand').show();
   
   $('.jquery-title').click(function(){   
       if($(this).next().is(':visible')){      
       	$(this).next().hide('fast');           
       }else{	
       	$('.jquery-body:visible').hide('fast');	
       	$(this).next().show('fast');					
       }
   });

   $('.jquery-collapse').click(function(){     
       $('.jquery-body').hide();   
       $('.jquery-collapse').hide();
       $('.jquery-expand').show();
   });

   $('.jquery-expand').click(function(){
       $('.jquery-body').show();
       $('.jquery-expand').hide();
       $('.jquery-collapse').show();
   });

});

