var AnSWFUInstance; // 全局的SWFU实例

/**
 * 上传错误提示信息
 */
var uploadErrorMsg = {
	'show' : function(msg){
		$("#wuQError").append(msg).show();
		return this;
	},
	'hide' : function(){
		$("#wuQError").hide("slow").text("");
		return this;
	}
};

/**
 * AnSWFUHandlers SWFU上传的处理方法
 */
var AnSWFUHandlers = {
	swfUploadLoaded : function () {
      swfUploadLoaded(this);
	},

	fileDialogStart : function() {
	  uploadErrorMsg.hide();
	},
	
	fileQueueError : function (file, errorCode, message) {
		try {
			var errorName = "";
			switch (errorCode) {
			case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
				errorName = "上传照片一次不能超过"+AnSWFUInstance.settings.file_upload_limit+"张，请重新选择。";
				break;
			case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
				errorName = "照片大小不能超过2M，请重新上传"+file.name;
				break;
			case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
				errorName = file.name+"的大小为0字节，无法上传，请选择其他文件。";
				break;
			case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
				errorName = file.name+"为不允许的文件类型，无法上传，请选择其他文件。";
				break;
			default:
				errorName = "未知错误，请重新选择。";
				break;
			}
			uploadErrorMsg.show(errorName);
		} catch (ex) {
			this.debug(ex);
		}
	},
	
	fileQueued : function (file) {
		try {
			// 显示队列数据
			queueData(file);
		} catch (ex) {
			this.debug(ex);
		}
	},

	fileDialogComplete : function (numFilesSelected, numFilesQueued) {
		try {
			// 隐藏错误信息
			if (numFilesSelected == numFilesQueued) uploadErrorMsg.hide();
		} catch (ex) {
			this.debug(ex);
		}
	},

	uploadStart : function (file) {
		try {
			// 上传到该文件时，隐藏该文件的删除按钮
			$("a.delete", $("#"+file.id)).hide();
		} catch (ex) {
			this.debug(ex);
		}

		return true;
	},

	uploadProgress : function (file, bytesLoaded, totalBytes) {
		try {
			queuePercent = Math.ceil(bytesLoaded / totalBytes * 100);
			$(".progressBar div", $("#"+file.id)).text(queuePercent+'%').css("width", queuePercent+"%");
			if (queuePercent == 100) {
				$(".progressBar div", $("#"+file.id)).text('上传完毕。');
			}
		} catch (ex) {
			this.debug(ex);
		}
	},

	uploadSuccess : function (file, serverData, receivedResponse) {
		try {
			//@TODO 上传成功，返回数据的操作 处理封面
			var attachmentInfo = serverData.split("|");
			var attachmentId = attachmentInfo[0];
			var attachmentPath = attachmentInfo[1];
			// 隐藏的附件ID
			$("#"+file.id).append('<input type="hidden" name="attachments[]" value="'+attachmentId+'" />');
			var newKey = parseInt($("#"+file.id).find(".viewOrder").val());
			$("#"+file.id).append('<input type="hidden" name="view_keys[]" value="'+ newKey +'" />');
			// cover的值
			$("input[type=radio]", $("#"+file.id)).val(attachmentPath);
		} catch (ex) {
			uploadErrorMsg.show('上传错误，请刷新页面重试上传！');
			this.debug(ex);
		}
	},

	uploadError : function (file, errorCode, message) {
		try {
			var errorName = "";
			switch (errorCode) {
			case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
				errorName = "HTTP ERROR";
				break;
			case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
				errorName = "MISSING UPLOAD URL";
				break;
			case SWFUpload.UPLOAD_ERROR.IO_ERROR:
				errorName = "IO ERROR";
				break;
			case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
				errorName = "SECURITY ERROR";
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
				errorName = "UPLOAD LIMIT EXCEEDED";
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
				errorName = "UPLOAD FAILED";
				break;
			case SWFUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND:
				errorName = "SPECIFIED FILE ID NOT FOUND";
				break;
			case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
				errorName = "FILE VALIDATION FAILED";
				break;
			case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
				//errorName = "FILE CANCELLED";
				return;
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
				errorName = "FILE STOPPED";
				break;
			default:
				errorName = "上传错误，请刷新页面重试上传！";
				break;
			}
			errorName += '<br />';
			uploadErrorMsg.show('上传错误，请刷新页面重试上传！');
			// 显示浏览按钮
			AnSWFUInstance.setButtonDisabled(false);
			// 显示清空按钮
			$("#btnClear").removeClass('btnClear').attr('disabled', '');
			// 显示删除按钮
			$("a.delete", $("#"+file.id)).show();
			// 使保存按钮生效
			$(':submit').val('更新').attr('disabled', '');
		} catch (ex) {
			this.debug(ex);
		}
	},

	uploadComplete : function (file) {
		return true;
	},

	// @require plugins/swfupload.queue.js
	queueComplete : function (file) {
		try {
			//this.debug(queuePercent);
			if (AnSWFUInstance.getStats().files_queued === 0) {
				// 等待图片上传完毕后提交表单
				$(':submit').val("处理数据中");
				$("form").submit();
				return false;
			} else {
				this.debug(queueBytesLoaded+'/'+'queueBytes');
				// 发生错误，显示清空按钮
				$("#btnClear").show();
			}
		} catch (ex) {
			this.debug(ex);
		}
	},

	// This custom debug method sends all debug messages to the Firebug console.  If debug is enabled it then sends the debug messages
	// to the built in debug console.  Only JavaScript message are sent to the Firebug console when debug is disabled (SWFUpload won't send the messages
	// when debug is disabled).
	debug : function (message) {
		try {
			if (window.console && typeofwindow.console.error === "function" && typeofwindow.console.log === "function") {
				if (typeofmessage === "object" && typeofmessage.name === "string" && typeofmessage.message === "string") {
					window.console.error(message);
				} else {
					window.console.log(message);
				}
			}
		} catch (ex) {
		}
		try {
			if (this.settings.debug) {
				this.debugMessage(message);
			}
		} catch (ex1) {
		}
	}
};

/**
 * swfupload初始化操作
 * @param swfuInstance
 * @return
 */
function swfUploadLoaded(swfuInstance){
	// 全局实例
	AnSWFUInstance = swfuInstance;
	$("form").submit(function(e){
		// 如果表单验证不通过 不能提交 和validate统一
		if(!$("form").valid()) {
          return false;
		}

        // 判断队列中是否有上传文件
		var statsObj = AnSWFUInstance.getStats();
		if (statsObj.files_queued != 0) {
			// 屏蔽浏览器默认行为
	        e.preventDefault();
	        // 设置浏览按钮disabled
	        AnSWFUInstance.setButtonDisabled(true);
			// 设置清空按钮失效
			$("#btnClear").addClass('btnClear').attr('disabled', 'disabled');
			// 屏蔽掉保存按钮
			$(':submit').val('上传中...').attr('disabled', 'disabled');
			// 开始上传
	        try {
	          AnSWFUInstance.startUpload();
	        } catch (ex) {
	      
	        }
	        return false;
		}
	});
	// 清空
	$('#btnClear').click(function(){
		// 判断队列是否正在上传
		var statsObj = AnSWFUInstance.getStats();
		if (statsObj.in_progress == 1) {
			uploadErrorMsg.show('文件正在上传中，无法清空');
			return false;
		}
		// 重置计数
		//queueBytes = 0;
		//queueItem.reset().show();
		// 从flash列队中移除
		// @require plugins/swfupload.queue.js
		AnSWFUInstance.cancelQueue();
		// 从html队列中移除
		$("tr", $('#uploaderQueue table')).slideUp('fast', function(){
			$(this).remove();
		});
	});
}

/**
 * 显示队列数据
 * @param fileData
 * @return
 */
function queueData(fileData, isDefault){
	// 生成队列html代码
	var queueHtml = queueHtmlGenerator(fileData, isDefault);

	// 将html代码插入到队列中
	var _elem = $(queueHtml);
	$('#uploaderQueue table').append(_elem);

	// 绑定删除事件
	$('a.delete', _elem).click(function(){
		$(this).closest('tbody').slideUp("fast", function(){
			$(this).remove();
		});
		AnSWFUInstance.cancelUpload(fileData.id);
	});

	_elem = null;
}

/**
 * 生成列队html代码
 * @param fileData
 * @return
 * @require plugins/swfupload.speed.js for SWFUpload.speed.formatSize()
 */
var insertIndex = 1;
function queueHtmlGenerator(fileData, isDefault){
	
	// 从flash选择的文件fileData中是没有title，intro和path属性的，只有默认数据才有
	if (fileData.title == undefined) fileData.title = '';
	if (fileData.intro == undefined) fileData.intro = '';
	if (fileData.path == undefined) fileData.path = '';
	if (fileData.cover == undefined) fileData.cover = '';
	if (fileData.extension == undefined) fileData.extension = '';
	if (fileData.description == undefined) fileData.description = '';
	var queueHtml = '';
	viewOrder++;
	queueHtml += '<tbody class="wuI"><tr id="'+fileData.id+'">';
	queueHtml += '<td class="u-name">'+fileData.name+'</td>';
	queueHtml += '<td class="u-size"><input style="display:none;" type="radio" name="cover" value="'+fileData.path+'"';
	if ((!isDefault &&insertIndex == 1) || (isDefault && fileData.cover == fileData.path)) {
      //queueHtml += 'checked="checked"';
	}
	//queueHtml += '/>设为封面</td>';
	queueHtml += '/></td>';
	
	queueHtml += '<td class="u-size">'+SWFUpload.speed.formatBytes(fileData.size)+'</td>';
	queueHtml += '<td class="progressBar" width="200px"><div style="background:blue;"></div></td>';
	queueHtml += '<td class="u-option"><a href="javascript:void(0);" class="delete">删除</a></td>';
	queueHtml += '<input class="oldViewOrder" type="hidden" value="'+viewOrder+'" name="default_view_order['+fileData.id+']">';
	queueHtml += '<input class="viewOrder" type="hidden" value="'+viewOrder+'" name="view_order[]" />';
	// 默认数据 name中不需要_index，后台做diff
	if (isDefault) queueHtml += '<input type="hidden" name="default_attachments[]" value="'+fileData.id+'" />';
	queueHtml += '</tr>';
	if (isDefault) {
	  queueHtml += '<tr><td>描述：</td><td colspan="4"><textarea name="default_description['+fileData.id+']">';
	} else {
	  queueHtml += '<tr><td>描述：</td><td colspan="4"><textarea name="description[]">';
	}
	if (fileData.description) {
	  queueHtml += fileData.description;
	}
	queueHtml += '</textarea></td></tr></tbody>';
	insertIndex++;
	return queueHtml;
}