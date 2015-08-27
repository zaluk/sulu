define([],function(){"use strict";var a={eventNamespace:"sulu.internal-links",resultKey:"nodes",idKey:"uuid",columnNavigationUrl:"",hideConfigButton:!0,hidePositionElement:!0,dataAttribute:"internal-links",actionIcon:"fa-link",dataDefault:[],translations:{noContentSelected:"internal-links.nolinks-selected",addLinks:"internal-links.add",visible:"public.visible",of:"public.of"}},b={data:function(a){return['<div id="',a.ids.columnNavigation,'"/>'].join("")},contentItem:function(a){return['<span class="value">',a,"</span>"].join("")}},c=function(a){return"#"+this.options.ids[a]},d=function(){this.sandbox.on("husky.overlay.internal-links."+this.options.instanceName+".add.initialized",f.bind(this)),this.sandbox.on("husky.column-navigation."+this.options.instanceName+".action",e.bind(this)),this.sandbox.on("husky.column-navigation."+this.options.instanceName+".initialized",function(){this.sandbox.emit("husky.overlay.internal-links."+this.options.instanceName+".add.set-position")}.bind(this))},e=function(a){var b=this.getData();-1===b.indexOf(a.id)&&(a.uuid=a.id,b.push(a.id),this.setData(b,!1),a.publishedState&&this.addItem(a))},f=function(){var a=this.getData();this.sandbox.start([{name:"column-navigation@husky",options:{el:c.call(this,"columnNavigation"),url:this.options.columnNavigationUrl,instanceName:this.options.instanceName,actionIcon:"fa-plus-circle",resultKey:this.options.resultKey,showOptions:!1,showStatus:!0,responsive:!1,skin:"fixed-height-small",markable:!0,sortable:!1,premarkedIds:a}}])},g=function(){var a=this.sandbox.dom.createElement("<div/>");this.sandbox.dom.append(this.$el,a),this.sandbox.start([{name:"overlay@husky",options:{triggerEl:this.$addButton,cssClass:"internal-links-overlay",el:a,container:this.$el,removeOnClose:!1,instanceName:"internal-links."+this.options.instanceName+".add",skin:"wide",slides:[{title:this.sandbox.translate(this.options.translations.addLinks),cssClass:"internal-links-overlay-add",data:b.data(this.options)}]}}])};return{type:"itembox",initialize:function(){this.options=this.sandbox.util.extend(!0,{},a,this.options),this.options.ids={container:"internal-links-"+this.options.instanceName+"-container",addButton:"internal-links-"+this.options.instanceName+"-add",configButton:"internal-links-"+this.options.instanceName+"-config",displayOption:"internal-links-"+this.options.instanceName+"-display-option",content:"internal-links-"+this.options.instanceName+"-content",chooseTab:"internal-links-"+this.options.instanceName+"-choose-tab",columnNavigation:"internal-links-"+this.options.instanceName+"-column-navigation"},this.render(),d.call(this),g.call(this)},getUrl:function(a){var b=-1===this.options.url.indexOf("?")?"?":"&";return[this.options.url,b,this.options.idsParameter,"=",(a||[]).join(",")].join("")},getItemContent:function(a){return b.contentItem(a.title)},sortHandler:function(a){this.setData(a,!1)},removeHandler:function(a){for(var b=this.getData(),c=-1,d=b.length;++c<d;)if(a===b[c]){b.splice(c,1);break}this.sandbox.emit("husky.column-navigation."+this.options.instanceName+".unmark",a),this.setData(b,!1)}}});