var YATV = {

	//var: 'value',

	check: function(action) {
		if (action=='delete') {
			checkdelete = confirm('Eintrag wirklich entfernen?');
		}
		return checkdelete;
	},

	status: function(status,id,field) {
		$('tr[data-id="'+id+'"]').find('a[data-field="'+field+'"]').prev().addClass(status);
		window.setTimeout('$(\'tr[data-id="'+id+'"]\').find(\'a[data-field="'+field+'"]\').prev().removeClass(\''+status+'\')', 1000);
	},

	action: function(action,tbl,id,primary,field,value) {
		$.post('inc/func.action.php', {action:action, tbl:tbl, id:id, primary:primary, field:field, value:value }, function(data) {
			if (data==1) {
				if (action=='delete') {
					$('tr[data-id="'+id+'"]').fadeOut(500,function() {
						$(this).remove();
						var count = parseInt($('.count').html());
						$('.count').html(--count);
					});
				} else if (action=='edit') {
					var el = $('tr[data-id="'+id+'"]').find('[data-field="'+field+'"]');
					if (!$('table').data('multiedit')) {
						el.parent().empty().html(value);
					} else {
						YATV.status('success',id,field);
					}
				}
			} else {
				YATV.status('error',id,field);
			}
		});
	}

};



$(function() {

	var baseurl = $('base').attr('href');

	$('select#tblselect').change(function() {
		var tbl = $('select#tblselect option:selected').text();
		var proto = window.location.protocol.slice(0,-1);
		var url = proto+'://'+window.location.hostname+window.location.pathname;
		window.location.href = url+'?tbl='+tbl;
	});

	$('select#pagination').change(function() {
		var parameters = $(this).val();
		window.location.href = baseurl+parameters;
	});

	$('input[type="text"]').keydown(function(e) {
		if (e.which==13) $(this).next().click();
	});

	$('a.edit, a.delete').click(function(e) {
		e.preventDefault();
		var el = $(this);
		var data_tbl = $('table').data();
		var id = el.closest('tr').data('id');
		var data = el.data();
		var value = el.prev().val();
		if (data.action=='delete') {
			if (!YATV.check(data.action)) return;
		}
		YATV.action(data.action,data_tbl.table,id,data_tbl.primary,data.field,value);
	});

});
