jQuery( document ).on( 'elementor/popup/show', function( event, popupData ) {
   //var popupId = popupData.id;
		 var popupStatusCheck= popupStatus.value;
	
	//number already verified alert
	var verificationAlert= popupStatus.verification_alert;
	if(verificationAlert==1){		
    alert('Your number is already verified!');
	} 
	//number now verified alert
	var verificationNowAlert= popupStatus.verification_now_alert;
	if(verificationNowAlert==1){		
    alert('Your number is verified now!');
	} 
	//sent otp
	var otpSendAlert= popupStatus.otp_send_alert;
	if(otpSendAlert==1){		
    alert('OTP number is sent to your phone please check and verify! ');
	} 
	//limit reached
	var limitReachedAlert= popupStatus.limit_reached_alert;
	if(limitReachedAlert==1){		
    alert('OTP Limit Reached! Again Enter Number! ');
	} 
			if(popupStatusCheck==1){
				//jQuery("#elementor-popup-modal-724").hide();	
			let popupId = $('.elementor-popup-modal').attr('id');
				jQuery("#"+popupId).hide();	
				console.log("popup hided");
				}
});
