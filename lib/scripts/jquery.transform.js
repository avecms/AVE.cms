(function($){
	var defaultOptions = {preloadImg:true};

	/***************************
	  Labels
	***************************/
	var jqTransformGetLabel = function(objfield){
		var selfForm = $(objfield.get(0).form);
		var oLabel = objfield.next();
		if(!oLabel.is('label')) {
			oLabel = objfield.prev();
			if(oLabel.is('label')){
				var inputname = objfield.attr('id');
				if(inputname){
					oLabel = selfForm.find('label[for="'+inputname+'"]');
				} 
			}
		}
		if(oLabel.is('label')){return oLabel.css('cursor','pointer');}
		return false;
	};

	/* Check for an external click */
	var jqTransformCheckExternalClick = function(event) {
		if ($(event.target).parents('.jqTransformSelectWrapper').length === 0) { jqTransformHideSelect($(event.target)); }
	};

	/* Apply document listener */
	var jqTransformAddDocumentListener = function (){
		$(document).on('mousedown', jqTransformCheckExternalClick);
	};

	/* Add a new handler for the reset action */
	var jqTransformReset = function(f){
		$('a.jqTransformCheckbox, a.jqTransformRadio', f).removeClass('jqTransformChecked');
		$('input:checkbox, input:radio', f).each(function(){if(this.checked){$('a', $(this).parent()).addClass('jqTransformChecked');}});
	};

	/***************************
	  Check Boxes 
	 ***************************/	
	$.fn.jqTransCheckBox = function(){
		return this.each(function(){
			if($(this).hasClass('jqTransformHidden')) {return;}

			var $input = $(this);

			//set the click on the label
			var oLabel = jqTransformGetLabel($input);
			oLabel && oLabel.click(function(){aLink.trigger('click');});

            if($input.attr('title') && $input.attr('class')){
			    var aLink = $('<a href="#" title="'+$input.attr('title')+'" class="jqTransformCheckbox '+$input.attr('class')+'"></a>');
            }else{
			    var aLink = $('<a href="#" class="jqTransformCheckbox"></a>');
            }

			//wrap and add the link

			if($input.hasClass('float')){
				$input.addClass('jqTransformHidden').wrap('<span class="jqTransformCheckboxWrapperFloat"></span>').parent().prepend(aLink);
			}else{
				$input.addClass('jqTransformHidden').wrap('<span class="jqTransformCheckboxWrapper"></span>').parent().prepend(aLink);
			};

			if($input.attr('disabled')){aLink.addClass('jqTransformCheckedDisable')};
			if($input.attr('disabled') && $input.attr('checked')){aLink.addClass('jqTransformCheckedDisableCheck')};

			//on change, change the class of the link
			$input.change(function(){
				this.checked && aLink.addClass('jqTransformChecked') || aLink.removeClass('jqTransformChecked');
				return true;
			});

			// Click Handler, trigger the click and change event on the input
			aLink.on('click', function(){
				//do nothing if the original input is disabled
				if($input.attr('disabled')){return false;}
				//trigger the envents on the input object
				$input.trigger('click').trigger("change");
				return false;
			});

			// set the default state
			this.checked && aLink.addClass('jqTransformChecked');
		});
	};

	/***************************
	  Radio Buttons 
	 ***************************/
	$.fn.jqTransRadio = function(){
		return this.each(function(){
			if($(this).hasClass('jqTransformHidden')) {return;}

			var $input = $(this);
			var inputSelf = this;

			oLabel = jqTransformGetLabel($input);
			oLabel && oLabel.click(function(){aLink.trigger('click');});
	
			var aLink = $('<a href="#" class="jqTransformRadio" rel="'+ this.name +'"></a>');
			$input.addClass('jqTransformHidden').wrap('<span class="jqTransformRadioWrapper"></span>').parent().prepend(aLink);

			$input.change(function(){
				inputSelf.checked && aLink.addClass('jqTransformChecked') || aLink.removeClass('jqTransformChecked');
				return true;
			});

			// Click Handler
			aLink.on('click', function(){
				if($input.attr('disabled')){return false;}
				$input.trigger('click').trigger('change');
	
				// uncheck all others of same name input radio elements
				$('input[name="'+$input.attr('name')+'"]',inputSelf.form).not($input).each(function(){
					$(this).attr('type')=='radio' && $(this).trigger('change');
				});
	
				return false;
			});
			// set the default state
			inputSelf.checked && aLink.addClass('jqTransformChecked');
		});
	};

		$.fn.jqTransform = function(options){
		var opt = $.extend({},defaultOptions,options);

		/* each form */
		 return this.each(function(){

			$('input:checkbox', this).jqTransCheckBox();
			$('input:radio', this).jqTransRadio();

		}); /* End Form each */

	};/* End the Plugin */

})(jQuery);