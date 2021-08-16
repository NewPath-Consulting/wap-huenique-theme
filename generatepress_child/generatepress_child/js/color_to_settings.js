//ajax code from https://stackoverflow.com/questions/22005582/ajax-post-in-wordpress
/*jQuery(document).ready( function() {

    //on 
    //#__wp-uploader-id-0 > div.media-frame-toolbar > div > div.media-toolbar-primary.search-form > button

    jQuery(".user_vote").click( function(e) {
       e.preventDefault(); 
       post_id = jQuery(this).attr("data-post_id")
       nonce = jQuery(this).attr("data-nonce")
 
       jQuery.ajax({
          type : "post",
          dataType : "json",
          url : myAjax.ajaxurl,
          data : {action: "my_user_vote", post_id : post_id, nonce: nonce},
          success: function(response) {
             if(response.type == "success") {
                jQuery("#vote_counter").html(response.vote_count)
             }
             else {
                alert("Your vote could not be added")
             }
          }
       })   
 
    })
 
 })*/