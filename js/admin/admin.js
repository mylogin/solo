/**
 *
 * This file is part of Solo CMS.
 * Licensed under the GPL version 2.0 license.
 * See COPYRIGHT and LICENSE files.
 *
 */

var Global = {};

function Tree() {
	this.to_open = [];
	this.to_select = [];
	this.single_selected = false;
	this.opened = {};
	this.selected = {};
	this.name = '';
	this.type = '';
	this.field_block = null;
	this.field_name = '';
	this.button = null;
	this.id_prototype = 0;
	this.hook_open = null;
	this.hook_close = null;
	this.load = function(id, elem) {
		var _this = this;
		var el = elem ? elem : $('#'+this.name+'_'+id);		
		if(this.opened[id] !== undefined) {			
			if(this.opened[id]) {
				this.opened[id] = 0;
				el.find('ul').eq(0).slideUp(200);
				if(!elem) {
					el.find('div').eq(0).removeClass('tree_open');
				}
				if(this.hook_close) {
					this.hook_close.call(_this, id, el);
				}		
			} else {
				this.opened[id] = 1;
				el.find('ul').eq(0).slideDown(200);
				if(!elem) {
					el.find('div').eq(0).addClass('tree_open');
				}
				if(this.hook_open) {
					this.hook_open.call(_this, id, el);
				}
			}
			return true;
		}		
		$.ajax({
			type: "post",
			dataType: "json",
			url: host+'/adm/structure/branch/',
			data: {id: id, type: this.type},
			success: function(a) {				
				_this.opened[id] = 1;
				if(_this.hook_open) {
					_this.hook_open.call(_this, id, el);
				}
				var ul = $('<ul style="display:none;"></ul>');
				el.append(ul);
				for(var i in a) {
					var button_style = '';
					var link_style = ''
					if(a[i]['count'] == 0) {
						button_style = ' style="background: none;"';
					}
					var li = '<li id="'+_this.name+'_'+a[i]['id']+'"><div'+button_style+'></div><span><a href="'+host+'/adm/structure/edit/'+a[i]['id']+'"'+link_style+'>'+a[i]['name']+'</a></span>';
					if(_this.type == 'structure') {
						li += '&nbsp;<a onclick="if(!confirm(\'Удалить?\')) return false;" href="'+host+'/adm/structure/del/'+a[i]['id']+'">Удалить</a>';
					}
					li += '</li>';
					li = $(li);
					li.find('div').click(function() {
						_this.load.call(_this, $(this).closest('li').attr('id').replace(_this.name+'_', ''));
					});
					if(_this.type != 'structure') {
						li.find('a').eq(0).click((function(id, name) {
							return function() {
								if(_this.selected[id]) {
									_this.deselect.call(_this, id);
								} else {
									_this.select.call(_this, id, name);
								}
								return false;
							}
						})(a[i]['id'], a[i]['name']));
					}
					ul.append(li);					
					if($.inArray(a[i]['id'], _this.to_select) != -1) {
						_this.select(a[i]['id'], a[i]['name']);
					}
				}				
				ul.slideDown(200);
				if(!elem) {
					el.find('div').eq(0).addClass('tree_open');
				}
				if(_this.to_open.length) {
					id = _this.to_open[0];
					_this.to_open.splice(0, 1);				
					_this.load.call(_this, id);
				}
			}	
		});
	}
	this.select = function(id, name) {
		if(this.single_selected) {
			for(var i in this.selected) {
				this.deselect(i);
			}
		}
		this.selected[id] = name;
		$('#'+this.name+'_'+id+' a').eq(0).css({background: '#366ea8', color: 'white'});
		this.selected_update();
	}
	this.deselect = function(id) {
		delete this.selected[id];
		$('#'+this.name+'_'+id+' a').eq(0).css({background: 'white', color: '#366ea8'});
		this.selected_update();
	}
	this.selected_update = function() {
		var _this = this;
		if(this.field_block && this.field_name) {
			this.field_block.empty();
			for(var i in this.selected) {
				var a = $('<div><input class="input" type="hidden" name="'+this.field_name+(!this.single_selected ? '[]' : '')+'" value="'+i+'" /><span>'+this.selected[i]+'</span> <a href="">X</a></div>');
				this.field_block.append(a);
				a.find('a').eq(0).click((function(id) {
					return function() {
						$(this).closest('div').remove();
						_this.deselect(id);
						return false;
					}
				})(i));
			}
		}
		if(this.hook_selected_update) {
			this.hook_selected_update(this.selected);
		}
	}
	this.clear = function() {
		for(var i in this.selected) {
			this.deselect(i);
		}
		for(var i in this.opened) {
			if(this.opened[i]) {
				this.opened[i] = 0;
				$('#'+this.name+'_'+i).find('ul').eq(0).slideUp(200);
			}
		}		
	}
	this.disable = function() {
		this.clear();
		this.button.addClass('js_link_inactive');
		this.button.unbind('click').click(function() {return false;});
	}
	this.enable = function() {
		this.clear();
		this.button.removeClass('js_link_inactive');
		this.button.unbind('click').click(this.button_handler);
	}
}


$(document).ready(function() {
	
	admin.init();

	var tree_main = $('#tree_main');
	if(tree_main.length) {
		var tree_main_obj = new Tree;
		tree_main_obj.name = 'tree_main';
		tree_main_obj.type = 'structure';
		tree_main_obj.to_open = Global.tree_struct_main_open ? Global.tree_struct_main_open : [];
		tree_main_obj.hook_open = function(id, elem) {
			$.ajax({
				type: "post",
				url: host+'/adm/structure/tree_main_state/',
				data: {id: id, type: 'open'},
				success: function(a) {}
			});
		}
		tree_main_obj.hook_close = function(id, elem) {
			$.ajax({
				type: "post",
				url: host+'/adm/structure/tree_main_state/',
				data: {id: id, type: 'close'},
				success: function(a) {}
			});
		}
		tree_main_obj.load(0, tree_main);
	}

	var st_tree_parent = $('#tree_struct_parent');
	if(st_tree_parent.length) {
		admin.st_tree_parent_obj = new Tree;
		var st_tree_parent_popup = new Popup;
		st_tree_parent_popup.static_top = 30;
		admin.st_tree_parent_obj.field_block = $('#tree_struct_parent_block');
		admin.st_tree_parent_obj.field_name = 'id_parent';
		admin.st_tree_parent_obj.to_open = Global.tree_struct_parent_open ? Global.tree_struct_parent_open : [];
		admin.st_tree_parent_obj.to_select = Global.tree_struct_parent_select ? Global.tree_struct_parent_select : [];
		admin.st_tree_parent_obj.name = 'st_tree_parent';
		admin.st_tree_parent_obj.type = 'page';
		admin.st_tree_parent_obj.single_selected = true;
		admin.st_tree_parent_obj.load(0, st_tree_parent);
		admin.st_tree_parent_obj.button = $('#tree_struct_parent_link');
		admin.st_tree_parent_obj.button_handler = function() {
			st_tree_parent_popup.show($('#tree_struct_parent'));
			return false;
		}
		admin.st_tree_parent_obj.button.click(admin.st_tree_parent_obj.button_handler);
	}

	var st_tree_proto = $('#tree_struct_prototype');
	if(st_tree_proto.length) {
		admin.st_tree_proto_obj = new Tree;
		var st_tree_proto_popup = new Popup;
		st_tree_proto_popup.static_top = 30;
		admin.st_tree_proto_obj.field_block = $('#tree_struct_prototype_block');
		admin.st_tree_proto_obj.field_name = 'id_prototype';
		admin.st_tree_proto_obj.to_select = Global.tree_struct_prototype_select ? Global.tree_struct_prototype_select : [];
		admin.st_tree_proto_obj.name = 'st_tree_proto';
		admin.st_tree_proto_obj.type = 'prototype';
		admin.st_tree_proto_obj.single_selected = true;
		admin.st_tree_proto_obj.hook_selected_update = function() {			
			var id_prototype = 0;
			for(id_prototype in admin.st_tree_proto_obj.selected) {
				if(!this.id_prototype) {
					this.id_prototype = id_prototype;
				}
				break;
			}			
			for(var i in admin.st_from_proto) {
				if(!admin.st_form[0].elements[i]) {
					continue;
				}
				admin.st_form[0].elements[i].checked = id_prototype ? (id_prototype == this.id_prototype ? admin.st_from_proto[i] : true) : false;
				admin.st_form[0].elements[i].disabled = !id_prototype ? true : false;
				admin.st_from_proto_handle.call(admin.st_form[0].elements[i]);
			}
		}
		admin.st_tree_proto_obj.load(0, st_tree_proto);
		admin.st_tree_proto_obj.button = $('#tree_struct_prototype_link');
		admin.st_tree_proto_obj.button_handler = function() {
			st_tree_proto_popup.show($('#tree_struct_prototype'));
			return false;
		}
		admin.st_tree_proto_obj.button.click(admin.st_tree_proto_obj.button_handler);
	}

	var tree_user_access = $('#tree_user_access');
	if(tree_user_access.length) {		
		var tree_user_access_obj = new Tree;
		var tree_user_access_popup = new Popup;
		tree_user_access_popup.static_top = 30;
		tree_user_access_obj.field_block = $('#tree_user_access_block');
		tree_user_access_obj.field_name = 'items';
		tree_user_access_obj.to_open = Global.tree_user_access_open ? Global.tree_user_access_open : [];
		tree_user_access_obj.to_select = Global.tree_user_access_select ? Global.tree_user_access_select : [];
		tree_user_access_obj.name = 'tree_user_access';
		tree_user_access_obj.type = 'page';
		tree_user_access_obj.single_selected = false;
		tree_user_access_obj.load(0, tree_user_access);
		$('#tree_user_access_link').click(function() {
			tree_user_access_popup.show($('#tree_user_access'));
			return false;
		});
	}

	var access_type = $('#access_type');
	if(access_type.length) {
		admin.access_type_change(access_type.val(), tree_user_access_obj);
		access_type.change(function() {
			admin.access_type_change($(this).val(), tree_user_access_obj, true)
		});
	}



});


var admin = {

	st_tree_parent_obj: null,
	st_tree_proto_obj: null,

	st_form: null,
	st_from_proto: {
		'from_prototype[way]': true, 
		'from_prototype[file]': true,
		'from_prototype[actions]': true,
		'from_prototype[template]': true
	},

	st_from_proto_handle: function() {
		if(this.checked) {
			$(this).closest('fieldset').find('input,select').not('[name="'+this.name+'"]').attr('disabled', 'disabled');
		} else {
			$(this).closest('fieldset').find('input,select').not('[name="'+this.name+'"]').not('.hidden').removeAttr('disabled');
		}
	},

	init: function() {

		if(Global.struct_pos === undefined) {
			return;
		}

		admin.st_form = $('#adm_structure_form');
		
		for(var i in admin.st_from_proto) {
			if(!admin.st_form[0].elements[i]) {
				continue;
			}
			admin.st_from_proto[i] = admin.st_form[0].elements[i].checked;
			admin.st_from_proto_handle.call(admin.st_form[0].elements[i]);
			admin.st_form[0].elements[i].onchange = admin.st_from_proto_handle;
		}

		if(admin.st_form[0].elements['is_prototype']) {
			admin.st_is_prototype_change.call(admin.st_form[0].elements['is_prototype']);
			admin.st_form[0].elements['is_prototype'].onchange = admin.st_is_prototype_change;
		}
						
	},
	st_is_prototype_change: function() {		
		if(admin.st_tree_parent_obj) {
			if(this.checked) {
				admin.st_tree_parent_obj.disable();
			} else {
				admin.st_tree_parent_obj.enable();
			}
		}
		if(admin.st_tree_proto_obj) {
			if(this.checked) {
				admin.st_tree_proto_obj.disable();
			} else {
				admin.st_tree_proto_obj.enable();
			}
		}
	},
	st_parent: false,
	st_input_one_field: function(wrap, name, val) {
		var block = $($('#st_wrap_way').find('.new').html()),
			input = block.find('input').eq(0),
			select = block.find('select');
		input.attr('name', name+'[]');
		if(name != 'path') {
			select.remove();
		} else {
			select.removeClass('hidden').removeAttr('disabled');
			if(val) {
				var pre = val.split(':');
				if(pre.length > 1) {
					if(pre[1] > 1) {
						val = '';
					}
					select.find("option").filter(function() {
						return $(this).val() == pre[1];
					}).prop('selected', true);
				}
			}
		}
		if(val) {			
			val = val.replace(/^p:1:/, '', val);
			input.val(val);
		}
		wrap.append(block);
	},	
	st_actions_field: function(wrap, val) {
		var block = $(wrap.find('.new').html()),
			select = block.find('select');
		select.removeClass('hidden').removeAttr('disabled');		
		select.find("option").filter(function() {
		    return $(this).val() == val;
		}).prop('selected', true);
		admin.st_actions_param.call(select[0]);
		select.click(admin.st_actions_param);
		wrap.append(block);
	},
	st_actions_param: function() {
		var selected = this.options[this.selectedIndex];
		var display = $(selected).hasClass('param') ? 'inline' : 'none';
		$(this).closest('div').find('a').css('display', display).attr('href', host+'/adm/structure/param/'+Global.struct_id+(Global.struct_tag ? '/'+Global.struct_tag : '')+'/'+selected.value);
	},
	access_type_change: function(type, tree_obj, change) {
		var link = $('#tree_user_access_link');
		if(change) {
			tree_obj.clear();
		}
		switch(parseInt(type)) {
			case 0:
				link.css({display: 'none'});
			break;
			case 1:
				link.css({display: 'inline'});
				link.html('Запрещенные элементы');
			break;
			case 2:
				link.css({display: 'inline'});
				link.html('Разрешенные элементы');
			break;
		}		
	}
};
