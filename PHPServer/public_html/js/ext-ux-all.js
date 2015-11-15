
//Resize containers if the window size changes
Ext.EventManager.onWindowResize(function () {
	Ext.ComponentManager.each(function(item){
		if(typeof(this[item].updateLayout) == 'function')
		{
			var xtype = this[item].xtype;
			var allowedXTypes = ['grid', 'mappanel', 'formgrid', 'panel', 'container', 'form'];
			if(allowedXTypes.indexOf(xtype) != -1){
				this[item].updateLayout();
			}
		}
	});
});

Ext.define('SystemFox.overrides.view.Table', {
    override: 'Ext.view.Table',
    checkThatContextIsParentGridView: function(e){
        var target = Ext.get(e.target);
        var parentGridView = target.up('.x-grid-view');
        if (this.el != parentGridView) {
            /* this is event of different grid caused by grids nesting */
            return false;
        } else {
            return true;
        }
    },
    processItemEvent: function(record, row, rowIndex, e) {
        if (e.target && !this.checkThatContextIsParentGridView(e)) {
            return false;
        } else {
            return this.callParent([record, row, rowIndex, e]);
        }
    }
});

Ext.define('Ext.ux.LocationField', {
	extend: 'Ext.form.field.TextArea',
	alias: 'widget.locationfield',
	initComponent: function(){
		//Assume the field is on a form object and add required hidden fields
		var form = this.findParentByType('form');
		form.add(
			Ext.create('Ext.form.field.Hidden', {
				name: 'latitude'
			})
		);
		form.add(
			Ext.create('Ext.form.field.Hidden', {
				name: 'longitude'
			})
		);
		this.callParent();
	},
	listeners: {
		blur: function(field, event, eOpts){
			var address = field.getValue();
			var form = field.findParentByType('form').getForm();
			form.findField('latitude').setValue(-1);
			form.findField('longitude').setValue(-1);
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode( { 'address': address}, Ext.Function.bind(function(results, status) {
			  if (status == google.maps.GeocoderStatus.OK) {
				if(results.length > 1)
				{
					Ext.MessageBox.show({
						title: "Error",
						msg: "Location is not specific enough.  Please enter a more specific location by providing the address, city, state, country and zipcode.",
						buttons: Ext.MessageBox.OK,
						icon: Ext.MessageBox.ERROR
					});
					return;
				}
				var f = this.findParentByType('form');
				var location = results[0].geometry.location;
				f.getForm().findField('latitude').setValue(location.lat());
				f.getForm().findField('longitude').setValue(location.lng());
			  } else {
				Ext.MessageBox.show({
					title: "Error",
					msg: "Location could not be found for the following reason: "+status,
					buttons: Ext.MessageBox.OK,
					icon: Ext.MessageBox.ERROR
				});
			  }
			}, field)
			);
		}
	},
	validator: function(value){
		var address = value;
		
		var form = this.findParentByType('form').getForm();
		var lat = form.findField('latitude').getValue();
		var lng = form.findField('longitude').getValue();
		if(lat == -1 || lng == -1)
			return 'Location could not be found';
		
		return true;
	}
});

Ext.define('Ext.ux.FormGrid',{
	extend: 'Ext.grid.GridPanel',
	alias: 'widget.formgrid',
	width: 'auto',
	forceFit: true,
	allowAdd: true,
	allowSave: true,
	allowRemove: true,
	allowExpandCollapseAll: true,
	formReadOnly: false,
	plugins: [],
	viewConfig: {
		listeners: {
			expandbody: function(rowNode, record, expandRow, eOpts){
				var row = 'rowExpander-row-' + record.get('id');
				if(Ext.get(row).dom.innerHTML == '')
				{
					var grid = this.findParentByType('grid');
					var formPanel = Ext.create("Ext.form.Panel", {
						style: {
							'overflow-y': 'auto',
							'max-height': '600px'
						},
						items: grid.GetFormItems(record)
					});
					formPanel.render(row);
					formPanel.loadRecord(record);
					
					if(this.grid.formReadOnly)
					{
						formPanel.getForm().getFields().each (function (field) {
						  field.setReadOnly(true);
						});
					}
				}
			},
			collapsebody: function(rowNode, record, expandRow, eOpts){
				if(this.grid.allowSave)
				{
					var row = 'rowExpander-row-' + record.get('id');
					var form = Ext.getCmp(Ext.get(row).dom.children[0].id);
					if(form.isValid())
					{
						form.updateRecord(record);
					}
					else
					{
						this.grid.plugins[0].toggleRow(this.grid.getStore().indexOfId(record.id),record);
						this.grid.allFormsValid = false;
						Ext.MessageBox.show({
							title: "Error",
							msg: "Form is invalid.  Please fix the errors.",
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.ERROR
						});
					}
				}
			}
		}
	},
	initComponent: function(){
		var rowExpander = Ext.create('Ext.ux.RowExpander', {
			rowBodyTpl: '<div id="rowExpander-row-{id}"></div>',
		});
		this.plugins.splice(0,0,rowExpander);//Row expander is assumed to be the first plugin.
		
		this.callParent();
		
		var toolbarItems = [];
		if(this.allowAdd)
		{
			toolbarItems.push({
				xtype: 'button',
				text: 'Add',
				iconCls: 'add-icon',
				handler : function() {
					var grid = this.findParentByType('grid');

					// Create a model instance
					var r = {};
					var store = grid.getStore();
					store.insert(0, r);
					
					grid.plugins[0].toggleRow(0, store.getAt(0));
				}
			});
		}
		if(this.allowRemove)
		{
			toolbarItems.push({
				xtype: 'button',
				itemId: 'remove',
				text: 'Remove',
				iconCls: 'remove-icon',
				handler: function() {
					var sm = this.findParentByType('grid').getSelectionModel();
					var store = this.findParentByType('grid').getStore();
					store.remove(sm.getSelection());
					if (store.getCount() > 0) {
						sm.select(0);
					}
				},
				disabled: true
			});
		}
		if(this.allowSave)
		{
			toolbarItems.push({
				xtype: 'button',
				itemId: 'save',
				text: 'Save',
				iconCls: 'save-icon',
				handler: function() {
					var grid = this.findParentByType('grid');
					grid.allFormsValid = true;
					grid.plugins[0].expandAll(false);
					if(grid.allFormsValid)
						grid.getStore().sync();
				}
			});
		}
		
		if(this.allowExpandCollapseAll)
		{
			toolbarItems.push({
				xtype: 'button',
				itemId: 'expandall',
				text: 'Expand All',
				iconCls: 'expandall-icon',
				handler: function(){
					var grid = this.findParentByType('grid');
					grid.plugins[0].expandAll(true);
				}
			});
		
			toolbarItems.push({
				xtype: 'button',
				itemId: 'collapseall',
				text: 'Collapse All',
				iconCls: 'collapseall-icon',
				handler: function(){
					var grid = this.findParentByType('grid');
					grid.plugins[0].expandAll(false);
				}
			});
		}
		
		var dockedItems = [{
			xtype: 'pagingtoolbar',
			store: this.store,
			dock: 'bottom',
			displayInfo: true
		}];
		
		if(toolbarItems.length != 0)
		{
			dockedItems.push({
				xtype: 'toolbar',
				dock: 'top',
				items:toolbarItems
			});
		}
		
		this.addDocked(dockedItems);
		
		if(this.allowRemove)
		{
			this.on('selectionchange', function(view, records) {
				this.down('#remove').setDisabled(!records.length);
			}, this);
		}
	},
	GetFormItems: function(){
		alert("Developer must provide this function");
	}
});

Ext.define('Ext.ux.RowExpander', {
	extend: 'Ext.grid.plugin.RowExpander',
	expandAll: function (expand) {
		expand = typeof expand !== 'undefined' ? expand : true;

		var grid = this.grid,
			store = grid.getStore(),
			rowExpander = grid.plugins[0],
			nodes = rowExpander.view.getNodes();

		for (var i = 0; i < nodes.length; i++) {
			var node = Ext.fly(nodes[i]);

			if (node.hasCls(rowExpander.rowCollapsedCls) === expand) {
				rowExpander.toggleRow(i, store.getAt(i));
			}
		}
	}
});

//Override to fix an IE issue where colspan can't be set to 0
Ext.define('Ext.grid.feature.RowBody', {
    override :'Ext.grid.feature.RowBody',

    onColumnsChanged: function (headerCt) {
		var items = this.view.el.query(this.rowBodyTdSelector),
			colspan = headerCt.getVisibleGridColumns().length,
			len = items.length,
			i;
		colspan = colspan == 0 ? 1 : colspan;
		for (i = 0; i < len; ++i) {
			items[i].colSpan = colspan;
		}
	}
});

/* Using this panel requires the following script include:
src="https://maps.googleapis.com/maps/api/js?sensor=false&libraries=geometry"
*/
Ext.define('Ext.ux.MapPanel', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.mappanel',
	keyStore: '',
	mapStore: '',
	markerWindowHeight: 250,
	markerWindowWidth: 600,
	keyWidth: 300,
	/*
	{
		filters: [
			{
				text: //Display Text
				emptytext: //works for number/string types
				dataIndex: //location to pull from the record
				type: //list, number, string etc
				store: //for list type
				operator: //The operator to apply to the field
			}
		]
	}
	*/
	filterMenuConfig: '', //Configuration for the filterMenu
	layout: 'border',
	height: 500,
	width: '100%',
	forcefit: true,
	getMapStoreForm: function(){
		return null;//Developer should implement this with the form they are using for the mapStore
	},
	
	initComponent: function(){
		this.callParent();
		if(this.keyStore == '' || this.mapStore == '')
			throw new Exception("Missing required keyStore or mapStore");
			
		this.filterMenuConfig.filterStore = this.mapStore;
			
		this.add({
			xtype: 'container',
			id: 'mapContainer',
			region: 'center',
			height: this.height,
			listeners:{
				resize: function(container, width, height, oldWidth, oldHeight, eOpts ){
					google.maps.event.trigger(container.findParentByType("mappanel").map, "resize");
				}
			}
		});
		
		if(this.keyStore.getCount() > 1)
		{
		
			var keyContainer = Ext.create('Ext.panel.Panel', {
				region: 'east',
				autoScroll: true,
				width: this.keyWidth,
				defaults: {
					padding: '0, 5, 0, 5'
				},
				title: 'Key',
				collapsible: true
			});
		
		
			this.keyStore.each(function(record){
				keyContainer.add(Ext.create('Ext.container.Container', {
					data: record,
					tpl: '<img src="'+Transition.global.imagesLocation+'{icon}"/><span>- {name}</span>'
				}));
			});
			
			this.add(keyContainer);
		}
		
		this.addDocked({
			xtype: 'toolbar',
			dock: 'top',
			items: [
				{ 
					text: 'Filters',
					iconCls: 'filter-icon',
					menu: Ext.create('Ext.ux.FilterMenu', this.filterMenuConfig)
				},
				{ 
					text: 'Clear Filters',
					iconCls: 'clearfilter-icon',
					handler: function(button, e){
						var mapPanel = this.findParentByType('mappanel');
						var store = mapPanel.mapStore;
						store.clearFilter();
						mapPanel.showMarkers(store);
					}
				}
			]
		});
	},
	afterRender: function(){
		this.callParent();
		
		var mapContainer = this.getComponent('mapContainer');
		
		var map_canvas = mapContainer.el.dom;
		var currentLat;
		var currentLng;
		var zoom = 6;
		if(Transition.user.hasLocation())
		{
			currentLat = Transition.user.lat;
			currentLng = Transition.user.lng;
		}
		else
		{
			currentLat = Transition.user.defaultLat;
			currentLng = Transition.user.defaultLng;
			zoom = 4;
		}
		var map_options = {
			center: new google.maps.LatLng(currentLat, currentLng),
			zoom: zoom,
			mapTypeId: google.maps.MapTypeId.ROADMAP //google.maps.MapTypeId.SATELLITE
		}
		this.map = new google.maps.Map(map_canvas, map_options);
		
		this.markerArray = [];
		for(var i = 0; i < this.mapStore.getCount(); i++)
		{
			var record = this.mapStore.getAt(i);
			var marker = new google.maps.Marker({
			  //icon: Transition.global.imagesLocation + record.get('icon'),
			  position: new google.maps.LatLng(record.get('latitude'), record.get('longitude')),
			  map: this.map,
			  title: record.get('name'),
			  id: record.get('id')
			});
			if(record.get('icon') != '')
			{
				marker.setIcon(Transition.global.imagesLocation + record.get('icon'));
			}
			this.markerArray.push(marker);
		}
		
		for(var i=0;i<this.markerArray.length;i++)
		{
			google.maps.event.addListener(this.markerArray[i], 'click', Ext.Function.pass( function (mapPanel) {
				mapPanel.openMarker(this);
			}, this));
		}
	},
	/*
		Displays all of the markers for the people in the people array
		@param people - an array of id's to display markers for
	*/
	showMarkers: function(store){
		for(var i=0;i<this.markerArray.length;i++)
		{
			if(store.findRecord('id',this.markerArray[i].id))
				this.markerArray[i].setMap(this.map);
			else this.markerArray[i].setMap(null);
		}
	},
	openMarker: function(marker){
		var store = this.mapStore;
		var record = store.findRecord('id', marker.id);
		var form = this.getMapStoreForm();
		if(form == null)
			return;
			
		var t = Ext.getCmp('markerWindow');
		if(t)
			t.destroy();
		
		var window = Ext.create('Ext.window.Window', {
			id: 'markerWindow',
			title: record.get('markertitle'),
			layout: 'fit',
			height: this.markerWindowHeight,
			width: this.markerWindowWidth,
			items: form
		});
		form.loadRecord(record);
		form.getForm().getFields().each (function (field) {
		  field.setReadOnly(true);
		});

		window.show();
		window.center();
	}
});

Ext.define('Ext.ux.FilterMenu', {
	extend: 'Ext.menu.Menu',
	alias: 'widget.filtermenu',
	filterStore: '',
	filters: [],
	initComponent: function(){
		this.callParent();
		
		/*
		filters: [
			{
				text: //Display Text
				emptytext: //works for number/string types
				dataIndex: //location to pull from the record
				type: //list, number, string etc
				store: //for list type
				operator: //The operator to apply to the field
			}
		]
		*/
		var filterMenuItems = this.filters;
		
		for(var i = 0; i<filterMenuItems.length; i++)
		{
			var filter = filterMenuItems[i];
			var filterMenu = Ext.create('Ext.menu.CheckItem',{
				xtype: 'menucheckitem',
				text: filter.text,
				name: filter.dataIndex,
				operator: filter.operator,
				menu: {
					items: this.buildItems(filter)
				}
			});
			this.add(filterMenu);
		}
		
		var apply = Ext.create('Ext.button.Button', {
			xtype: 'button',
			text: 'Apply Filters',
			handler: function(button, e){
				var mapPanel = this.findParentByType('mappanel');
				var store = mapPanel.mapStore;
				store.clearFilter();
				
				var menu = button.findParentByType('menu');
				menu.items.each(function(item){
					if(item.xtype == 'menucheckitem' && item.checked)
					{
						var checkedItems = [];
						var innerType;
						item.menu.items.each(function(item){
							if(item.xtype=='menucheckitem' && item.checked)
							{
								checkedItems.push(item.recordid);
							}
							else if(item.xtype=='numberfield')
							{
								checkedItems.push(item.getValue());
							}
							innerType=item.xtype;
						});
						var filter;
						if(innerType == 'menucheckitem')
						{
							filter = new Ext.util.Filter({
								property: item.name,
								value: checkedItems,
								operator: item.operator
							});
						}
						else if(innerType == 'numberfield')
						{
							filter = new Ext.util.Filter({
								property: item.name,
								value: checkedItems[0],
								operator: item.operator
							});
						}
						store.addFilter(filter);
					}
				});
				
				mapPanel.showMarkers(store);
				menu.hide();
			}
		});
		this.add(apply);
	},
	buildItems: function(item){
		var items;
		switch(item.type)
		{
			case 'list':
				items = this.buildListItem(item);
				break;
			case 'number':
				items = this.buildNumberItem(item);
				break;
			case 'string':
				items = this.buildStringItem(item);
				break;
			default: 
				throw new Exception("Type not supported: "+ item.type);
		}
		return items;
	},
	buildListItem: function(item){
		var items = [];
		
		var store = item.store;
		store.each(function(record){
			items.push({
				xtype: 'menucheckitem',
				text: record.get('name'),
				recordid: record.get('id'),
				handler: function(item, e){
					//Check the parent item if this item is checked.
					if(item.checked)
						item.ownerCt.ownerCmp.setChecked(true);
				}
			});
		});
		
		return items;
	},
	buildNumberItem: function(item){
		var items = [];
		
		items.push({
			xtype: 'numberfield',
			emptyText: item.emptyText,
			listeners: {
				change: function(field, newValue, oldValue, eOpts){								
					field.ownerCt.ownerCmp.setChecked(true);
				}
			}
		});
		
		return items;
	},
	buildStringItem: function(item){
		var items = [];
		
		return items;
	},
	getStore: function(){
		return this.filterStore;
	}
});





Ext.ux.RatingColumn = Ext.extend(Ext.grid.ActionColumn, {
	size: 5,        // Number of icons in the column
	sortable: true,
	inconIndexRe: /ux-rating-icon-(\d+)/,
	hideable:false,

	constructor: function(config) {
		var items = config.items = [],
			i = 1,
			l = ((config.size || this.size) + 1);

		var tooltips = ['Beginner','Intermediate','Advanced','Expert','Master'];
			
		for (; i < l; i++) {
			items.push({tooltip: tooltips[i-1]});
		}
		Ext.ux.RatingColumn.superclass.constructor.call(this, config);
		this.renderer = Ext.Function.createInterceptor(this.renderer, this.setItemClasses);//this.renderer.createInterceptor(this.setItemClasses);
	},

	// When we are initialized as a plugin, hook into the grid's render evet
	init: function(grid) {
		if(!this.readOnly)
		{
			grid.on({
				render: this.onHostGridRender,
				single: true
			});
		}
	},

	// Route mousemove and mouseout events of the Grid's body through the plugins processEvent
	onHostGridRender: function(g) {
		g.el.on({
			mousemove: function(e) {
				g.getPlugins()[1].processEvent('mousemove', null, null, null, null, e);
			},
			mouseout: function(e) {
				g.getPlugins()[1].processEvent('mouseout', null, null, null, null, e);
			}
		})
	},

	setItemClasses: function(v) {
		for (var i = 0, it = this.items, l = it.length; i < l; i++) {
			it[i].iconCls = 'ux-rating-icon ux-rating-icon-' + (i + 1);
			if (i < v) {
				it[i].iconCls += ' ux-rating-icon-on';
			}
		}
		return true;
	},

	processEvent: function(evtName, view, cell, rowIndex, colIndex, e, record, row) {
		if(this.readOnly)
			return Ext.ux.RatingColumn.superclass.processEvent.apply(this, arguments); //Ignore events if this is readonly
		var t,match, iconIdx, icons, i, rec;
		
		if (typeof(e.getTarget) != 'undefined') 
		{
			t = Ext.get(e.getTarget());
		}

		switch (evtName) {
			case 'mouseout':
				if (t.dom.tagName != 'img') {
					if(typeof(this.activeCell) != 'undefined')
					{
						var obj = this.activeCell.select('img');
						obj.synchronize();//Synchronize the dom with the Ext data since it falls out of sync for some reason...
						obj.removeCls('ux-rating-icon-hover');
					}
					delete this.activeCell;
					delete this.ignoreMouseMove;
				}
				break;
			case 'mousemove':
				if (match = t.dom.className.match(this.inconIndexRe)) {
					this.activeCell = t.up('');
					if ((iconIdx = parseInt(match[1], 10)) != this.ignoreMouseMove) {
						delete this.ignoreMouseMove;
						icons = this.activeCell.query('img');
						for (i = 0; i < icons.length; i++) {
							var obj = Ext.fly(icons[i]);
							obj.synchronize();//Synchronize the dom with the Ext data since it falls out of sync for some reason...
							obj[(i < iconIdx) ? 'addCls' : 'removeCls']('ux-rating-icon-hover');
						}
					}
				}
				break;
			case 'click':
				if (match = t.dom.className.match(this.inconIndexRe)) {
					rec = record;
					if ((iconIdx = parseInt(match[1], 10)) == rec.get(this.dataIndex)) {
						rec.set(this.dataIndex, 0);
					} else {
						rec.set(this.dataIndex, iconIdx);
					}
					this.ignoreMouseMove = iconIdx;
				}
			default:
		} // End switch

//      Return any event handler return statuses to honour event cancelling
		return Ext.ux.RatingColumn.superclass.processEvent.apply(this, arguments);
	}
});

Ext.define('proxy.customphp', { 
	alias: 'proxy.customphp', 
	extend: 'Ext.data.proxy.Ajax', 
	useDefaultXhrHeader: false, 
	url: Transition.global.siteLocation,
	actionMethods: {
		create: 'POST', 
		read: 'POST', 
		update: 'POST', 
		destroy: 'POST'
	},
	api: {
		create  : Transition.global.siteLocation + '?action=create',
		read    : Transition.global.siteLocation + '?action=read',
		update  : Transition.global.siteLocation + '?action=update',
		destroy : Transition.global.siteLocation + '?action=delete'
	},
	extraParams: {
		id1: Transition.user.id,
		id2: Transition.user.id2
	},
	batchOrder: 'update,destroy,create',
	listeners: { 
		exception: function(proxy, response, options) {
			this.requestMessageProcessor(proxy, response);
		}
	},
	afterRequest: function(request, success) {
		this.requestMessageProcessor(request._scope, request._operation._response);
	},
	writer: {
		type: 'json',
		writeAllFields: true,
		rootProperty: 'data',
		encode: true,
		allowSingle: false
	},
	requestMessageProcessor: function(proxy, response) {
		if (response && proxy) {			
			try {						
				var responseData = JSON.parse(response.responseText);
				
				if (responseData.errortxt) {
					var messageDescription = 'Information'; // title of the alert box
					var messageIcon = Ext.MessageBox.INFO;
					
					if (!responseData.success)
					{
						var messageDescription = 'Error';
						var messageIcon = Ext.MessageBox.ERROR;
					}
					
					Ext.MessageBox.show({
						title: messageDescription,
						msg: responseData.errortxt,
						buttons: Ext.MessageBox.OK,
						icon: messageIcon
					});
				}
			}
			catch(err) {
				// Malformed response most likely
				console.log(err);
			}
		}
	}
});