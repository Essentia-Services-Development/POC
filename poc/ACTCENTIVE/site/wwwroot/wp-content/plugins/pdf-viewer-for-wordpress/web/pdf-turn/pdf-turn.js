  /********************************************************/
 /**     HERE MAIN MODIFIED PART FOR turnjs SUPPORT     **/
/********************************************************/
/// requires jQuery and turnjs
/// all code added in viewer.js (from pdfjs build) in order to support 
/// flipbook is commented with 'jQueryFB:' string to allow to find it easilly 

var bookFlip = {
	_width: [],		//flipbook pages width
	_height: [],	//flipbook pages height
	active: false,	//flipbook mode on
	_spreadBk: NaN,	//spread mode backup to restore
	_evSpread: null,//spread mode changed default event handler 
	_spread: NaN,	//spread page mode
	toStart: false,	//PDFjs require flipbook at start
	_intoView: null,//link handler default function
	_visPages: null,//visible pages function
	_ready: false,	//ready to start flipbook

	// event listeners when bookFlip need different handling 
	init: function(){
		jQuery(document).on('rotationchanging', () => {this.rotate()});
		jQuery(document).on('scalechanging', () => {this.resize()});
		jQuery(document).on('pagechanging', () => {this.flip()});
		//jQuery(document).on('pagenumberchanged', () => {this.flip()});
		
		jQuery(document).on('documentinit', () => {
			this.stop();
			this._ready = false;
		});

		jQuery(document).on('scrollmodechanged', () => {
			var scroll = PDFViewerApplication.pdfViewer.scrollMode;
			if (scroll === 3)this.start();
			else this.stop();
			var button = PDFViewerApplication.appConfig.secondaryToolbar.bookFlipButton;
			button.classList.toggle('toggled', scroll === 3);
		});

		jQuery(document).on('presentationmodechanged', () => {
			var new_pres_mode = PDFViewerApplication.pdfViewer.presentationModeState;

			if( new_pres_mode == 2){
				this.spread(0);
				PDFViewerApplication.eventBus.dispatch('spreadmodechanged', {
					source: PDFViewerApplication,
					mode: 0
				});
			} else if( new_pres_mode == 1 ) {
				this.spread(tnc_spread_default);
				PDFViewerApplication.eventBus.dispatch('spreadmodechanged', {
					source: PDFViewerApplication,
					mode: tnc_spread_default
				});
			}
		});
		
		jQuery(document).on('switchspreadmode', (evt) => {
			this.spread(evt.originalEvent.detail.mode);
			PDFViewerApplication.eventBus.dispatch('spreadmodechanged', {
				source: PDFViewerApplication,
				mode: evt.originalEvent.detail.mode
			});
		});
		
		jQuery(document).on('pagesloaded', () => {
			this._ready = true;
			if(this.toStart){
				this.toStart = false;
				PDFViewerApplication.pdfViewer.scrollMode = 3;
			}
		});

		jQuery(document).on('baseviewerinit', () => {

			PDFViewerApplicationOptions.set('scrollModeOnLoad',3);
				
			this._intoView = PDFViewerApplication.pdfViewer.scrollPageIntoView;
			this._visPages = PDFViewerApplication.pdfViewer._getVisiblePages;
		});
	},
	// startup flipbook
	start: function(){
		if(this.active || !this._ready)return;
		this.active = true;
		
		var viewer = PDFViewerApplication.pdfViewer;
		
		jQuery('.scrollModeButtons').removeClass('toggled');
		jQuery("#pvfw-next-page").hide();
		jQuery("#pvfw-previous-page").hide();
		jQuery("#pvfw-flip-next-page").show();
		jQuery("#pvfw-flip-previous-page").show();
		
		this._spreadBk = tnc_spread_default;
		// this._spreadBk = viewer.spreadMode;
		var selected = jQuery('.spreadModeButtons.toggled').attr('id');
		// this._spread = (this._spreadBk !== 2) ? 0 : 2;
		this._spread = this._spreadBk;
		viewer.spreadMode = 0;
		viewer._spreadMode = -1;
		jQuery('.spreadModeButtons').removeClass('toggled');
		jQuery('#' + selected).addClass('toggled');	
		
		this._evSpread = PDFViewerApplication.eventBus._listeners.switchspreadmode;
		PDFViewerApplication.eventBus._listeners.switchspreadmode = null;
		
		viewer.scrollPageIntoView = (data) => {return this.link(data)};
		viewer._getVisiblePages = () => {return this.load()};
		
		var scale = viewer.currentScale;
		
		var parent = this;
		jQuery('#viewer .page').each(function(){
			parent._width[jQuery(this).attr('data-page-number')] = jQuery(this).width() / scale;
			parent._height[jQuery(this).attr('data-page-number')] = jQuery(this).height() / scale;
		});
		
		jQuery('#viewer').removeClass('pdfViewer').addClass('bookViewer').css({ opacity: 1 });;
		
		// jQuery('#spreadOdd').prop('disabled', true);
		// var pages = PDFViewerApplication.pagesCount;
		// for(var page = 3; page < pages + (pages%2); page ++){
		// 	if(this._height[page]!=this._height[page-1] || this._width[page]!=this._width[page-1]){
		// 		jQuery('#spreadEven').prop('disabled', true);
		// 		this._spread = 0;
		// 	}
		// }
		if(window.location.hash) {
			var hash = window.location.hash.substr(1);

			var hash_param = hash.split('&').reduce(function (res, item) {
				var parts = item.split('=');
				res[parts[0]] = parts[1];
				return res;
			}, {});
			if( hash_param.page ){
				page_to_open = hash_param.page;
			} else {
				page_to_open = PDFViewerApplication.page;
			}
		} else {
			page_to_open = PDFViewerApplication.page;
		}
		jQuery('#viewer').turn({
			elevation: 50,
			width:  this._size(PDFViewerApplication.page,'width') * this._spreadMult(),
			height: this._size(PDFViewerApplication.page,'height'),
			page: page_to_open,
			when: {
				turning: function(event, page, view) {  
					var audio = document.getElementById("audio");
					audio.play();
				},
				turned: function(event, page) {
						var numPages = PDFViewerApplication.pagesCount;
						if((page > numPages) || (page < 1)){
							return;
						}
						PDFViewerApplication.page = page;
					//PDFViewerApplication.page = page;
					viewer.update();
				},
			},
			display: this._spreadType()
		});
		this.resize();
	},
	// shutdown flipbook
	stop: function(){
		if(!this.active)return;
		this.active = false;
		
		var viewer = PDFViewerApplication.pdfViewer;
		
		jQuery('#viewer').turn('destroy');
		
		viewer.scrollPageIntoView = this._intoView;
		viewer._getVisiblePages = this._visPages;
		
		PDFViewerApplication.eventBus._listeners.switchspreadmode = this._evSpread;
		viewer.spreadMode = this._spreadBk;

		jQuery("#pvfw-next-page").show();
		jQuery("#pvfw-previous-page").show();
		jQuery("#pvfw-flip-next-page").hide();
		jQuery("#pvfw-flip-previous-page").hide();
		
		jQuery('#viewer .page').removeAttr('style');
		jQuery('#viewer').removeAttr('style').removeClass('shadow bookViewer').addClass('pdfViewer');
		
		var parent = this;
		jQuery('#viewer .page').each(function(){
			var page = jQuery(this).attr('data-page-number');
			jQuery(this).css( 'width', parent._size(page,'width')).css( 'height', parent._size(page,'height'));
		});
		
	},
	// resize flipbook pages
	resize: function(){
		if(!this.active)return;
		var viewer = PDFViewerApplication.pdfViewer;

		var page = PDFViewerApplication.page;
		var page_width 	= this._size(page,'width') * this._spreadMult();
		var page_height = this._size(page,'height');
		var window_width = window.innerWidth;
		var window_height = window.innerHeight;

		var scale  = viewer.currentScale;

		var pageWidthScale = (window_width - 35 ) / page_width * scale;
		var pageHeightScale = ( window_height - 35 ) / page_height * scale;
		var flip_scale_max = Math.max(pageWidthScale, pageHeightScale);
		var flip_scale_min = Math.min(pageWidthScale, pageHeightScale);

		if( window.innerWidth < 600 ){
			if( viewer.currentScale < flip_scale_min){
				viewer.currentScale = flip_scale_min;
			}
		}else {
			viewer.currentScale = scale;
		}
		// else {
		// 	if(page_width > window_width){
		// 		viewer.currentScale = flip_scale_min;
		// 	}
		// }

		var page_width 	= this._size(page,'width') * this._spreadMult();
		var page_height = this._size(page,'height');

		jQuery('#viewer').turn('size', page_width, page_height);
	},
	// rotate flipbook pages
	rotate: function(){
		if(!this.active)return;
		[this._height, this._width] = [this._width, this._height];
		this.resize();
	},
	// change flipbook spread mode
	spread: function(spreadMode){
		if(!this.active)return;
		this._spread = spreadMode;
		var viewer = PDFViewerApplication.pdfViewer;
		jQuery('#viewer').turn('display', this._spreadType());
		this.resize();
	},
	// turn page
	flip: function(){
		if(!this.active)return;
		jQuery('#viewer').turn('page', PDFViewerApplication.page);
		if(!PDFViewerApplication.pdfViewer.hasEqualPageSizes)this.resize();
	},
	// follow internal links
	link: function(data){
		if(!this.active)return;
		PDFViewerApplication.page = data.pageNumber;
	},
	// load pages near shown page
	load: function(){
		if(!this.active)return;
		var views = PDFViewerApplication.pdfViewer._pages;
		var arr = [];
		var page = PDFViewerApplication.page;
		var min = Math.max(page - ((this._spread === 0) ? 2 : 3 + (page%2)), 0);
		var max = Math.min(page + ((this._spread === 0) ? 1 : 3 - (page%2)), views.length);
		for (var i = min, ii = max; i < ii; i++) 
		{
			arr.push({
				id: views[i].id,
				view: views[i],
				x: 0, y: 0, percent: 100
			});
		}

		let t= { first:arr[page - min - 1], last:arr[arr.length-1], views:arr };
		return t;
	},
	_spreadType: function(){
		if(window.innerWidth < 600){
			return 'single';
		} else {
			return (this._spread === 0) ? 'single' : 'double';
		}
	},
	_spreadMult: function(){
		if(window.innerWidth < 600){
			return 1;
		} else {
			return (this._spread === 0) ? 1 : 2;
		}
	},
	_size: function(page,request){
		var size;
		if (request === 'width') size = this._width[page];
		if (request === 'height') size = this._height[page];
		return size * PDFViewerApplication.pdfViewer.currentScale;
	}
};

bookFlip.init();