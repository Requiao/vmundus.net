 $(document).ready(function() {
	//create variable
	$('#var_name_input').on('paste', function() {
		setTimeout(function () {
			var variable = $('#var_name_input').val();
			variable = variable.replace(/ /g, "_").replace(/[\.']/g, "").toLowerCase();
			$('#var_name_input').val(variable);
		});
	});
 
	//add new word
	$('#add_word').on('click', function() {
		var file_id = $('#file_id').children(':selected').val();
		var var_name = $('#var_name_input').val();
		var word = $('#word_input').val();
		
		var data = new FormData();
		data.append('word', word);
		data.append('var_name', var_name);
		data.append('file_id', file_id);
		data.append('action', 'add_new_word');
		var url = "../etc/translation";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#anwd_reply').empty();
			if(temp.success == true) {
				$('#anwd_reply').html(temp.msg);
				$('#anwd_reply').css('color', 'green');
				
				$('#added_words').append('<div class="word_div">' +
										 '<input class="wd_var_name" value="' + temp.var_name + '">' +
										 '<input class="wd_word" value="' + ($("<textarea/>").html(temp.word).text()) + '">' +
										 '<p class="wd_edit">Edit</p>' +
										 '<p hidden>' + temp.word_id + '</p>' +
										 '<p class="wd_delete">Delete</p>' +
										 '</div>');
			}
			else {
				$('#anwd_reply').html(temp.error);
				$('#anwd_reply').css('color', 'rgb(207, 28, 28)');
			}
		}
		submitData(data, url, dataReply);
	});
	
	$('#container').on('click', '.wd_edit', function() {
		var word_id = $(this).next().html();
		var var_name = $(this).prev().prev().val();
		var word = $(this).prev().val();
		var e = this;
		var data = new FormData();
		data.append('word_id', word_id);
		data.append('var_name', var_name);
		data.append('word', word);
		data.append('action', 'edit_word');
		var url = "../etc/translation";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#anwd_reply').empty();
			if(temp.success == true) {
				$('#anwd_reply').html(temp.msg);
				$('#anwd_reply').css('color', 'green');
				
				$(e).prev().prev().val(temp.var_name);
				$(e).prev().val(temp.word);
			}
			else {
				$('#anwd_reply').html(temp.error);
				$('#anwd_reply').css('color', 'rgb(207, 28, 28)');
			}
		}
		submitData(data, url, dataReply);
	});
	
	$('#container').on('click', '.wd_delete', function() {
		var word_id = $(this).prev().html();
		var e = this;
		var data = new FormData();
		data.append('word_id', word_id);
		data.append('action', 'delete_word');
		var url = "../etc/translation";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#anwd_reply').empty();
			if(temp.success == true) {
				$('#anwd_reply').html(temp.msg);
				$('#anwd_reply').css('color', 'green');
				
				$(e).parent().remove();
			}
			else {
				$('#anwd_reply').html(temp.error);
				$('#anwd_reply').css('color', 'rgb(207, 28, 28)');
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* language pages */
	$('#alang_id').on('change', function() {
		var lang_id = $(this).children(':selected').val();
		var data = new FormData();
		data.append('lang_id', lang_id);
		data.append('action', 'get_pages');
		var url = "../etc/translation";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#afile_id').empty();
			if(temp.success == true) {
				$('#afile_id').append('<option></option>');
				for(var x = 0; x < temp.pages.length; x++) {
					$('#afile_id').append('<option value="' + temp.pages[x].file_id + '">' + temp.pages[x].file_name + '</option>');
				}
				$('#afile_id').fadeIn();
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* get words */
	$('#afile_id').on('change', function() {
		var lang_id = $('#alang_id').children(':selected').val();
		var file_id = $(this).children(':selected').val();
		var data = new FormData();
		data.append('lang_id', lang_id);
		data.append('file_id', file_id);
		data.append('action', 'get_words');
		var url = "../etc/translation";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('#translate_div').empty();
			if(temp.success == true) {
				for(var x = 0; x < temp.words.length; x++) {
					if(temp.words[x].old) {
						var old = 'old_translation';
					}
					else {
						var old = '';
					}
					if(temp.mod == true) {
						var translator = '<a class="translator" href="user_profile?id=' + temp.words[x].translator + '">' +
										 temp.words[x].translator + '</a>';
					}
					else {
						var translator = '';
					}
					
					if(temp.base == true) {
						$('#translate_div').append('<div class="tword_divs">' +
												   '<p class="td_date">' + temp.words[x].date + " " + temp.words[x].time + '</p>' +
												   translator +
												   '<p class="td_reply"></p>' +
												   '<input class=wd_var_name ' +
												   '" value="' + temp.words[x].var_name + '">' +
												   '<input class=wd_word ' + 
												   '" value="' + ($("<textarea/>").html(temp.words[x].word).text()) + '">' +
												   '<p class="wd_edit">Edit</p>' +
												   '<p hidden>' + temp.words[x].word_id + '</p>' +
												   '<p class="wd_delete">Delete</p>' +
												   '</div>');
					}
					else {
						$('#translate_div').append('<div class="tword_divs">' +
												   '<p class="td_reply"></p>' +
												   '<p class="td_word">' + temp.words[x].word + '</p>' +
												   '<input class="td_translate_word ' + old + 
												   '" value="' + ($("<textarea/>").html(temp.words[x].translated).text()) + '">' +
												   '<p class="td_submit">Submit</p>' +
												   '<p hidden>' + temp.words[x].word_id + '</p>' +
												   '</div>');
					}
				}
			}
		}
		submitData(data, url, dataReply);
	});
	
	/* translate */
	$('#container').on('click', '.td_submit', function() {
		var lang_id = $('#alang_id').children(':selected').val();
		var word_id = $(this).next().html();
		var word = $(this).prev().val();
		var e = this;
		var data = new FormData();
		data.append('word_id', word_id);
		data.append('word', word);
		data.append('lang_id', lang_id);
		data.append('action', 'translate_word');
		var url = "../etc/translation";
		function dataReply(reply) {
			console.log(reply);
			var temp = JSON.parse(reply);
			$('.td_reply').empty();
			if(temp.success == true) {
				$(e).parent().find('.td_reply').html(temp.msg);
				$(e).parent().find('.td_reply').css('color', 'green');
				
				$(e).prev().val(($("<textarea/>").html(temp.word).text()));
			}
			else {
				$(e).parent().find('.td_reply').html(temp.error);
				$(e).parent().find('.td_reply').css('color', 'rgb(207, 28, 28)');
			}
		}
		submitData(data, url, dataReply);
	});
 });