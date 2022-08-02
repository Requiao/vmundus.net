/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for a single toolbar row.
	config.toolbarGroups = [
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'colors', groups: [ 'colors' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'others', groups: [ 'others' ] },
		{ name: 'about', groups: [ 'about' ] }
	];  

	//basic toolbar
	config.toolbar_basic = [
		{ name: 'document', items: [ 'Source', '-', 'Save', 'NewPage', 'Preview', 'Print' ] },
		{ name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', '-', 'Undo', 'Redo' ] },
		{ name: 'editing', items: [ 'SelectAll' ] },
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
		{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
		{ name: 'links', items: [ 'Link', 'Unlink' ] },
		{ name: 'insert', items: [ 'Image', 'Table', 'Smiley' ] },
		{ name: 'styles', items: [ 'Format', 'Font', 'FontSize', 'lineheight' ] },
		{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
		{ name: 'about', items: [ 'About' ] }
	];
	
	config.toolbar = null;
	
	// Dialog windows are also simplified.
	config.removeDialogTabs = 'link:advanced;link:target;';
	config.removeDialogTabs += 'image:advanced;image:Link';
	
	CKEDITOR.on('dialogDefinition', function(ev) {
	  var dialogName = ev.data.name;

	  if (dialogName == 'link') {
		var dialogDefinition = ev.data.definition;
		
		/*var targetTab = dialogDefinition.getContents('target');
		var targetOption = targetTab.get('linkTargetType');
		targetOption['items'] = [['_blank', '_blank']];*/
		
		// Get a reference to the "Link Info" tab.
        var infoTab = dialogDefinition.getContents('info');
        
        // Get a reference to the link type
        var linkOptions = infoTab.get('linkType');
		linkOptions['items'] = [['URL', 'url']];
		
		var linkOptions = infoTab.get('protocol');
		linkOptions['items'] = [['http://', 'http://'], ['https://', 'https://']];
	  }
	});
	
	//extra plugins
	config.extraPlugins = 'autolink';
	config.extraPlugins += ',clipboard';
	config.extraPlugins += ',notification';
	config.extraPlugins += ',fakeobjects';
	config.extraPlugins += ',link';
	config.extraPlugins += ',entities';
	config.extraPlugins += ',sourcearea';
	config.extraPlugins += ',colorbutton';
	config.extraPlugins += ',panelbutton';
	config.extraPlugins += ',floatpanel';
	config.extraPlugins += ',panel';
	config.extraPlugins += ',font';
	config.extraPlugins += ',richcombo';
	config.extraPlugins += ',listblock';
	config.extraPlugins += ',format';
	config.extraPlugins += ',image';
	config.extraPlugins += ',justify';
	config.extraPlugins += ',lineheight';
	config.extraPlugins += ',liststyle';
	config.extraPlugins += ',contextmenu';
	config.extraPlugins += ',menu';
	config.extraPlugins += ',magicline';
	config.extraPlugins += ',newpage';
	config.extraPlugins += ',nbsp';
	config.extraPlugins += ',pastefromword';
	config.extraPlugins += ',preview';
	config.extraPlugins += ',print';
	config.extraPlugins += ',removeformat';
	config.extraPlugins += ',selectall';
	config.extraPlugins += ',tab';
	config.extraPlugins += ',table';
	config.extraPlugins += ',tableresize';
	config.extraPlugins += ',tabletools';
	config.extraPlugins += ',tableselection';
	config.extraPlugins += ',wordcount';
	config.extraPlugins += ',htmlwriter';
	config.extraPlugins += ',undo';
	config.extraPlugins += ',blockquote';
	config.extraPlugins += ',smiley';
	config.extraPlugins += ',bidi';
	config.extraPlugins += ',enterkey';
	
	
	//plugin configuration
	config.removeButtons = 'Anchor';
	config.height = '550px';
	config.format_tags = 'p;h1;h2;h3;h4;h5;h6;pre;address';
	config.line_height="1em;1.5em;2em;2.5em;3em";
	config.smiley_columns = 7;
	
	CKEDITOR.config.font_names = 'Arial/Arial, Helvetica, sans-serif;' +
								 'Comic Sans MS/Comic Sans MS, cursive;' +
								 'Courier New/Courier New, Courier, monospace;' +
								 'Georgia/Georgia, serif;' +
								 'Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;' +
								 'Tahoma/Tahoma, Geneva, sans-serif;' +
								 'Times New Roman/Times New Roman, Times, serif;' +
								 'Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;' +
								 'Verdana/Verdana, Geneva, sans-serif';
	
	config.wordcount = {
		// Whether or not you want to show the Paragraphs Count
		showParagraphs: true,

		// Whether or not you want to show the Word Count
		showWordCount: true,

		// Whether or not you want to show the Char Count
		showCharCount: true,

		// Whether or not you want to count Spaces as Chars
		countSpacesAsChars: true,

		// Whether or not to include Html chars in the Char Count
		countHTML: true,
	
		// Maximum allowed Word Count, -1 is default for unlimited
		maxWordCount: -1,

		// Maximum allowed Char Count, -1 is default for unlimited
		maxCharCount: 21000,

		//do not block editor after reaching the limit
		hardLimit: false,
		
		// Add filter to add or remove element before counting (see CKEDITOR.htmlParser.filter), Default value : null (no filter)
		filter: new CKEDITOR.htmlParser.filter({
			elements: {
				div: function( element ) {
					if(element.attributes.class == 'mediaembed') {
						return false;
					}
				}
			}
		})
	};
	
	config.contentsCss = '../ckeditor/contents.css';
};
