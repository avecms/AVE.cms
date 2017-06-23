$(document).ready(function() {
	$(".field_numeric").keydown(function(event) {
		var num_dot = $(this).attr('data-num-dot');
		var keyCode = window.event ? event.keyCode : event.which;
		var foo = 0;
		// prevent if already dot
		if (keyCode != 8 && keyCode != 46) {
			if ((foo == 0) && (keyCode != 190) && (keyCode < 96 || keyCode > 105) && (keyCode < 46 || keyCode > 59)) {
				event.preventDefault();
			} // prevent if not number/dot
		}
		if ($(this).val().indexOf('.') > -1) {
			if (keyCode == 190)	event.preventDefault();
		}
		$(this).keyup(function() {
			this.value = this.value.replace(/[^0-9.]/i, "");
			if($(this).val().indexOf('.')!=-1){
				if($(this).val().split(".")[1].length >= num_dot){
					if( isNaN( parseFloat( this.value ) ) ) return;
					this.value = parseFloat(this.value).toFixed(num_dot);
				}
			}
			return this;
		});
	});
});