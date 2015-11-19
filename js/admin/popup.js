/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

var Popup = function() {
	this.version = 1.02;
	this.block = null;
	this.overlay = null;
	this.padding = 20;
	this.scroll_top = true;
	this.scroll_left = true;	
	this.hook_before_hide_block = null;
	this.hook_after_hide_block = null;
	this.key_down_ctrl_left = null;
	this.key_down_ctrl_right = null;
	this.hook_before_block_center = null;
	this.static_top = null;
	this.static_left = null;
	
	this.opt = {
		overlay: true,
		scroll: true,
		resize: true
	};
}
Popup.prototype.show_overlay = function(callback) {
	var _this = this;		
	this.overlay = $('<div></div>').css({
		opacity: 0,
		top: 0,
		left: 0,
		filter: 'alpha(opacity=0)', 			
		background: 'black',
		position: 'absolute',
		zIndex: 500
	});
	this.overlay_center();
 	$('body').append(this.overlay);
 	//$(window).bind('resize scroll', function() {_this.overlay_center.call(_this);});
	$(window).bind('resize', function() {_this.overlay_center.call(_this);});
 	$(this.overlay).bind('click', function() {_this.hide.call(_this);}); 	
	this.overlay.animate({opacity: 0.7}, 50, function() {callback.call(_this);});
}
Popup.prototype.hide_overlay = function() {
	var _this = this;	
	this.overlay.animate({opacity: 0}, 50, function() {
		//$(window).unbind('resize scroll');
		$(window).unbind('resize');
		$(this.overlay).unbind('click');
		_this.overlay.remove();
		_this.overlay = null;
	});
}
Popup.prototype.show_block = function(callback) {
	var _this = this;
	this.block.css({
		opacity: 0,
		filter: 'alpha(opacity=0)',
		display: 'block',
		position: 'absolute',
		zIndex: 600
	});		
	this.block_center(true);						
	//var event = ['scroll', 'resize'];
	var event = ['resize'];
	for(var i in event) {
		if(!this.opt[event[i]]) {
			event.splice(i, 1);
		}
	}
	if(event.length) {
		$(window).bind(event.join(' '), function() {_this.block_center.call(_this);});
	}
	$(document).bind('keydown', function(e) {_this.handle_key_down.call(_this, e);});	
	this.block.animate({opacity: 1}, 100, function() {if(callback) {callback.call(_this);}});
}

Popup.prototype.hide_block = function(callback) {
	var _this = this;
	this.call_in_array(this.hook_before_hide_block);
	this.block.animate({opacity: 0}, 100, function() {
		_this.block.css({display: 'none'});
		//var event = ['scroll', 'resize'];
		var event = ['resize'];
		for(var i in event) {
			if(!_this.opt[event[i]]) {
				event.splice(i, 1);
			}
		}
		if(event.length) {
			$(window).unbind(event.join(' '));
		}
		$(document).unbind('keydown');
		_this.call_in_array(_this.hook_after_hide_block);
		if(callback) {
			callback.call(_this);
		}
	});
}
Popup.prototype.show = function(block, callback) {
	this.block = block;
	if(this.opt.overlay && !this.overlay) {
		this.show_overlay(function() {this.show_block(callback);});
	}
	else {
		this.show_block(callback);
	}		
}
Popup.prototype.hide = function(callback) {
	if($.isFunction(callback)) {
		this.hide_block(callback);
	}
	else if(this.overlay) {
		this.hide_block(this.hide_overlay);
	}
	else {
		this.hide_block();
	}		
}
Popup.prototype.get_pos = function() {
	var w = $(window);
	return {			
		wh: w.height(),
		bh: this.block.height(),
		ww: w.width(),
		bw: this.block.width(),
		st: w.scrollTop(),
		sl: w.scrollLeft()
	}
}
Popup.prototype.block_center = function(open) {
	var pos = this.get_pos();
	if(this.hook_before_block_center) {
		this.hook_before_block_center(pos);
	}
	var pos = this.get_pos();
	var css = {};
	if(this.static_top !== null) {
		css.top = this.static_top;
	} else {
		if(pos.bh + this.padding < pos.wh) {
			css.top = (pos.wh - pos.bh) / 2 + pos.st;
		} else {
			if(open === true) {
				css.top = pos.st + this.padding;
			}			
		}
	}
	if(this.static_left !== null) {
		css.top = this.static_left;
	} else {
		if(pos.bw + this.padding < pos.ww) {
			css.left = (pos.ww - pos.bw) / 2 + pos.sl;			
		}
		else {
			if(open === true) {
				css.left = pos.sl + this.padding;
			}						
		}
	}
	if(css.top || css.left) {
		this.block.css(css);
	}			 	 		
}
Popup.prototype.overlay_center = function() {
	this.overlay.css({
		//width: $(window).width() + $(window).scrollLeft() + 'px', 
		//height: $(window).height() + $(window).scrollTop() + 'px'
		width: $(document).width() + 'px',
		height: $(document).height() + 'px'
	});		
}
Popup.prototype.handle_key_down = function(e) {		
	if(e.keyCode == 27) {
		this.hide();
	}
	if(this.key_down_left && e.keyCode == 37) {
		this.key_down_left();
	}
	if(this.key_down_right && e.keyCode == 39) {
		this.key_down_right();
	}
	if(this.key_down_ctrl_left && e.keyCode == 37 && e.ctrlKey) {
		this.key_down_ctrl_left();
	}
	if(this.key_down_ctrl_right && e.keyCode == 39 && e.ctrlKey) {
		this.key_down_ctrl_right();
	}
}
Popup.prototype.to_array = function(elem) {
	if(!$.isArray(elem)) {
		elem = [elem];
	}
	return elem;	
}
Popup.prototype.call_in_array = function(a) {
	if(a) {
		a = this.to_array(a);
		for(var i in a) {
			a[i]();
		}
	}
}