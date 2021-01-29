opoink = {
	formKey: '',
	url: {
		getUrl: function(Path){
			var bUrl = $(location).attr('protocol') + "//" + $(location).attr('hostname') + Path;
			return bUrl;
		},
		
		redirect: function(Path){
			window.location.href = this.getUrl(Path);
		},
		
		redirectLink: function(link){
			window.location.href = link;
		}
	},

	install: {
		phpVersion: false,
		memoryLimit: false,
		next: function(current){
			$('.install-box-' + current).addClass('hidden');
			current++;
			$('.install-box-' + current).removeClass('hidden');
			
			var percent = 20 * (current - 1);
			$('.install-box .progress-bar').css({
				width: percent + '%'
			});
		},
		
		chekcrequirements: function(stepNO){
			if(this.phpVersion == true && this.memoryLimit == true){
				this.next(stepNO);
			}
			else {
				$.ajax({					
					type: 'GET',
					url: opoink.url.getUrl('/system/install/requirement'),
					data: '',
					dataType: 'json',
					beforeSend: function() { 
						$('.install-box-2 .fa-spinner').addClass('fa-spin');
					},
					success: function(responseData) {
						$('.install-box-2 .fas').removeClass('fa-spin');
						$('.install-box-2 .fas').removeClass('fa-spinner');
						
						
						if(opoink.install.cmpVersions(responseData.phpversion, '7.1.0') >= 0){
							$('#phpVersion .fas').addClass('fa-check');
							opoink.install.phpVersion = true;
						} else {
							$('#phpVersion .fas').addClass('fa-times');
						}
						
						if(parseInt(responseData.memory_limit) >= 128){
							$('#memoryLimit .fas').addClass('fa-check');
							opoink.install.memoryLimit = true;
						} else {
							$('#memoryLimit .fas').addClass('fa-times');
						}
						
						$('#phpVersion .caption').text('Php Version 7.1.x - Your PHP version is ' + responseData.phpversion);
						$('#memoryLimit .caption').text('Memory limit 128M - Your PHP memory limit is ' + responseData.memory_limit);
						
						if(opoink.install.phpVersion == true && opoink.install.memoryLimit == true){
							$('.install-box .checkRequirements').text('Next');
						}
					},
					error: function(error){
					},
					complete: function(){
						$('.install-box-2 .fa-spinner').removeClass('fa-spin');
					}
				});
			}
		},
		
		saveDatabaseInfo: function(stepNo){
			var data = $('#database_form_field').serialize() + '&form_key=' + opoink.formKey;
			var sn = stepNo;
			$.ajax({					
				type: 'POST',
				url: opoink.url.getUrl('/system/install/database'),
				data: data,
				dataType: 'json',
				beforeSend: function() {
					opoink.std.showLoader();
					$('.databaseSaveErrorMessage').empty();
				},
				success: function(responseData) {
					if(responseData.error === 0){
						$('.install-box-3 .installNextStep').removeClass('hidden');
						$('.install-box-3 .saveDatabaseInfo').addClass('hidden');
					}
					$('.databaseSaveErrorMessage').append(responseData.message);
				},
				error: function(error){
				},
				complete: function(){
					opoink.std.hideLoader();
				}
			});
		},
		
		saveAdminAccount: function(stepNo){
			var data = $('#system_admin_account').serialize() + '&form_key=' + opoink.formKey;
			var sn = stepNo;
			$.ajax({					
				type: 'POST',
				url: opoink.url.getUrl('/system/install/saveadmin'),
				data: data,
				dataType: 'json',
				beforeSend: function() {
					opoink.std.showLoader();
					$('.install-box-4 input').removeClass('is-invalid');
					$('#saa_email .invalid-feedback').text('Email is required');
					$('#saa_retype_password .invalid-feedback').text('Retype password is required');
				},
				success: function(responseData) {
					if(responseData.error === 0){
						$('.install-box-4 .installNextStep').removeClass('hidden');
						$('.install-box-4 .saveAdminAccount').addClass('hidden');
					} 
					else {
						if(responseData.message === 'firstname') {
							$('#saa_firstname input').addClass('is-invalid');
						}
						else if(responseData.message === 'lastname') {
							$('#saa_lastname input').addClass('is-invalid');
						}
						else if(responseData.message === 'email') {
							$('#saa_email input').addClass('is-invalid');
						}
						else if(responseData.message === 'password') {
							$('#saa_password input').addClass('is-invalid');
						}
						else if(responseData.message === 'retypepassword') {
							$('#saa_retype_password input').addClass('is-invalid');
						}
						else if(responseData.message === 'invalidemail') {
							$('#saa_email input').addClass('is-invalid');
							$('#saa_email .invalid-feedback').text('Invalid email, please type correct email address');
						}
						else if(responseData.message === 'retypepasswordnotmatch') {
							$('#saa_retype_password input').addClass('is-invalid');
							$('#saa_retype_password .invalid-feedback').text('Password and retype password not match');
						}
					}
				},
				error: function(error){
				},
				complete: function(){
					opoink.std.hideLoader();
				}
			});
		},
		saveAdminUrl: function(stepNo){
			var data = $('#admin_panel_info').serialize() + '&form_key=' + opoink.formKey;
			var sn = stepNo;
			$.ajax({					
				type: 'POST',
				url: opoink.url.getUrl('/system/install/saveadminurl'),
				data: data,
				dataType: 'json',
				beforeSend: function() {
					opoink.std.showLoader();
				},
				success: function(responseData) {
					if(responseData.error === 0){
						$('.install-box-5 .installNextStep').removeClass('hidden');
						$('.install-box-5 .saveAdminUrl').addClass('hidden');
						$('.install-box-6 .aUrl a').text(opoink.url.getUrl('/admin') + $('#admin_panel_info input[name=admin_url]').val());
						$('.install-box-6 .aUrl a').prop('href', opoink.url.getUrl('/admin') + $('#admin_panel_info input[name=admin_url]').val());

						$('.install-box-6 .sUrl a').text(opoink.url.getUrl('/system') + $('#admin_panel_info input[name=system_url]').val());
						$('.install-box-6 .sUrl a').prop('href', opoink.url.getUrl('/system') + $('#admin_panel_info input[name=system_url]').val());

						$('.install-box-6 .sau').text('System Admin Email: ' + $('#system_admin_account input[name=email]').val());
						$('.install-box-6 .sap').text('System Admin Password: ' + $('#system_admin_account input[name=password]').val());
					}
				},
				error: function(error){
				},
				complete: function(){
					opoink.std.hideLoader();
				}
			});
		},
		
		/**
		*	compare version
		*/
		cmpVersions: function(a, b) {
			var i, diff;
			var regExStrip0 = /(\.0+)+$/;
			var segmentsA = a.replace(regExStrip0, '').split('.');
			var segmentsB = b.replace(regExStrip0, '').split('.');
			var l = Math.min(segmentsA.length, segmentsB.length);

			for (i = 0; i < l; i++) {
				diff = parseInt(segmentsA[i], 10) - parseInt(segmentsB[i], 10);
				if (diff) {
					return diff;
				}
			}
			return segmentsA.length - segmentsB.length;
		}
	},
	
	sysSetting: {
		save: function(){
			var data = $('#sys_settings_form_field').serialize() + '&form_key=' + opoink.formKey;
			
			$.ajax({					
				type: 'POST',
				url: opoink.url.getUrl('/system'+sysRoute+'/settings'),
				data: data,
				dataType: 'json',
				beforeSend: function() {
					opoink.std.showLoader();
				},
				success: function(responseData) {
					alert('Settings successfully saved.');
				},
				error: function(error){
				},
				complete: function(){
					opoink.url.redirect('/system'+$('#sys_settings_form_field input[name=system_url]').val()+'/settings');
				}
			});
		}
	},

	cache: {
		beforeFormSubmit: function(checkbox, formId){
			selectedCount = $('.'+checkbox+':checked').length;
			
			if (selectedCount > 0){
				$("#mainModalDialog").modal('hide');
			    opoink.std.showLoader();
			    opoink.std.requestFormKey(opoink.cache.formSubmit, formId);
			} else {
				$("#mainModalDialog .modal-body").text('No module was selected.');
				$("#mainModalDialog .modal-footer").addClass('hidden');
			}
		}, 
		formSubmit: function (formId){
			$(".form_key").val(opoink.formKey);
			$('#'+formId).submit();
		}
	},
	
	std: {
		requestFormKey: function(callBackFunction, options){
			$.ajax({					
				type: 'POST',
				url: opoink.url.getUrl('/system'+sysRoute+'/install/formkey'),
				data: {},
				dataType: 'json',
				beforeSend: function() {
					opoink.std.showLoader();
				},
				success: function(responseData) {
					opoink.formKey = responseData.formKey;
					if(callBackFunction != false){
						callBackFunction(options);
					}
				},
				error: function(error){
				},
				complete: function(){
					/** opoink.std.hideLoader(); */
				}
			});
		},
		
		showLoader: function(){
			$('.preloader').css({'display': 'block'});
		},
		
		hideLoader: function(){
			$('.preloader').css({'display': 'none'});
		}
	},

	user: {
		beforeSave: function(){
			$("#mainModalDialog").modal('hide');
			opoink.std.showLoader();
			opoink.std.requestFormKey(opoink.user.save, false);
		},
		save:function(){
			$(".form_key").val(opoink.formKey);
			$('#user_account_form').submit();
		}
	},

	module: {
		beforeFormSubmit: function(checkbox, formId){
			selectedCount = $('.'+checkbox+':checked').length;
			
			if (selectedCount > 0){
				$("#mainModalDialog").modal('hide');
			    opoink.std.showLoader();
			    opoink.std.requestFormKey(opoink.module.formSubmit, formId);
			} else {
				$("#mainModalDialog .modal-body").text('No module was selected.');
				$("#mainModalDialog .modal-footer").addClass('hidden');
			}
		}, 

		formSubmit: function(options){
			if($.isArray(options)){
				var formId = null;
				var action = null;
				$.each(options, function( key, value ) {
					if(key === 0){
						formId = value;
					}
					if(key === 1){
						action = value;
					}
				});
				if(formId != null && action != null){
					$(".form_key").val(opoink.formKey);
					$(".action").val(action);

					$('#'+formId).submit();
				} else {
					opoink.std.hideLoader();
					$("#mainModalDialog").modal('show');
					$("#mainModalDialog .modal-body").text('Cannot send request please reload your browser and try again.');
					$("#mainModalDialog .modal-footer").addClass('hidden');
				}
			} else {
				$(".form_key").val(opoink.formKey);
				$('#'+options).submit();
			}
		}
	}
}

$('.install-box .installNextStep').on('click', function(){
	var stepNo = this.dataset.stepno;
	opoink.install.next(stepNo);
});
$('.install-box .checkRequirements').on('click', function(){
	var stepNo = this.dataset.stepno;
	opoink.install.chekcrequirements(stepNo);
});
$('.install-box .saveDatabaseInfo').on('click', function(){
	opoink.std.requestFormKey(opoink.install.saveDatabaseInfo, '');
});
$('.install-box .saveAdminAccount').on('click', function(){
	opoink.std.requestFormKey(opoink.install.saveAdminAccount, '');
});
$('.install-box .saveAdminUrl').on('click', function(){
	opoink.std.requestFormKey(opoink.install.saveAdminUrl, '');
});
$('.notificationMessageClose').on('click', function(){
	this.parentElement.remove();
});
$('#sys_settings_form_field button.save').on('click', function(e){
	e.preventDefault();
	opoink.std.requestFormKey(opoink.sysSetting.save, '');
});

$('.multiCheboxSelect').on('click', function(e){
	var listProp = this.checked;
	$('.'+this.dataset.list).prop('checked', listProp);
});

$('.modalDialodConfirm').on('click', function(e){
	e.preventDefault();
	$("#mainModalDialog .modal-body").text(this.dataset.message);
	$("#mainModalDialog #modalTitle").text(this.dataset.title);
	$("#mainModalDialog .modal-footer").removeClass('hidden');
	$("#mainModalDialog .modal-footer .btn-secondary").text(this.dataset.closecaption);
	$("#mainModalDialog .modal-footer .btn-primary").text(this.dataset.confirm);
	$("#mainModalDialog .modal-footer .btn-primary").attr('onclick', this.dataset.callbackbunction);
	$("#mainModalDialog").modal('show');
});