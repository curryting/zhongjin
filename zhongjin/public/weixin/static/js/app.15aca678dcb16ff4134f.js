webpackJsonp([1],{"0QGf":function(t,e,s){"use strict";var i=function(){var t=this,e=t.$createElement;return(t._self._c||e)("div",{ref:"wrapper"},[t._t("default")],2)},a=[],n={render:i,staticRenderFns:a};e.a=n},"0jG4":function(t,e,s){"use strict";var i=s("mtWM"),a=s.n(i);e.a={company:{getContent:function(t){return a.a.post("/weixin/profile/index",t)}},product:{getName:function(t){return a.a.post("/weixin/product/lists",t)},getList:function(t){return a.a.post("/weixin/product/investLists",t)},getDetail:function(t){return a.a.post("/weixin/product/detail",t)},getReport:function(t){return a.a.post("xxxx",t)},getHistoryReport:function(t){return a.a.post("/weixin/product/reportLists",t)}},news:{getList:function(t){return a.a.post("/weixin/news/lists",t)},getDetail:function(t){return a.a.post("/weixin/news/detail",t)},sendMsg:function(t){return a.a.post("/weixin/news/comment",t)},replyComment:function(t){return a.a.post("/weixin/news/reply",t)},getReply:function(t){return a.a.post("/weixin/news/readReply",t)},getComments:function(t){return a.a.post("/weixin/news/readComment",t)}},notice:{},vote:{},survey:{getList:function(t){return a.a.post("/weixin/survey/list",t)},getDetail:function(t){return a.a.post("/weixin/survey/detail",t)}}}},"24iT":function(t,e){},"2Ccr":function(t,e,s){"use strict";e.a={props:{tip:{type:String,default:""}}}},"2gjM":function(t,e){},"5R2O":function(t,e){},"7RoA":function(t,e,s){"use strict";function i(t){s("NPyl")}var a=s("fNuo"),n=s("VPwr"),r=s("VU/8"),o=i,c=r(a.a,n.a,!1,o,"data-v-c7527804",null);e.a=c.exports},"7Z49":function(t,e,s){"use strict";var i=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"news-detail"},[i("scroll-view",{staticClass:"scroll-wrapper",attrs:{data:t.commentsList,pullup:t.pullup},on:{scrollToEnd:t.getMoreComments}},[i("div",{staticClass:"scroll-content"},[i("div",{staticClass:"detail-main"},[i("p",{staticClass:"title"},[t._v(t._s(t.newsDetail.title))]),t._v(" "),i("div",{staticClass:"time-author"},[i("span",[t._v(t._s(t.newsDetail.timestamp))]),t._v(" "),i("span",[t._v(t._s(t.newsDetail.author))])]),t._v(" "),i("div",{staticClass:"abstract"},[t._v(t._s(t.newsDetail.abstract))]),t._v(" "),i("div",{staticClass:"detail-content",domProps:{innerHTML:t._s(t.newsDetail.content)}})]),t._v(" "),i("div",{staticClass:"detail-comment"},[i("ul",{staticClass:"divide-line"},[i("li"),t._v(" "),i("li",[t._v("评论")]),t._v(" "),i("li")]),t._v(" "),i("div",{staticClass:"leave-comment"},[i("a",{directives:[{name:"show",rawName:"v-show",value:!t.showLeaveMsg&&!t.isAdmin,expression:"!showLeaveMsg&&!isAdmin"}],staticClass:"edit",attrs:{href:"javascript:"},on:{click:t.showInput}},[i("img",{attrs:{src:s("tCTf"),alt:"icon"}}),t._v(" "),i("span",[t._v("评论")])]),t._v(" "),i("div",{directives:[{name:"show",rawName:"v-show",value:t.showLeaveMsg,expression:"showLeaveMsg"}],staticClass:"form"},[i("form",{attrs:{onsubmit:"return false"}},[i("div",{ref:"textarea",staticClass:"input",attrs:{contenteditable:"true"},on:{blur:t.hideInput,input:t.getText}}),t._v(" "),i("button",{attrs:{type:"submit"},on:{click:t.sendMsg}},[t._v("发送")])])])]),t._v(" "),i("ul",{staticClass:"comments"},t._l(t.commentsList,function(e){return i("li",{key:e.id,staticClass:"item"},[i("div",{staticClass:"avatar"},[i("img",{attrs:{src:e.avatar,alt:"头像"}})]),t._v(" "),i("div",{staticClass:"text"},[i("span",{staticClass:"nickname"},[t._v(t._s(e.name))]),t._v(" "),i("p",{staticClass:"message"},[t._v(t._s(e.content))]),t._v(" "),i("span",{staticClass:"time"},[t._v(t._s(e.timestamp))]),t._v(" "),i("div",{directives:[{name:"show",rawName:"v-show",value:t.isAdmin,expression:"isAdmin"}],staticClass:"reply-btn"},[i("a",{attrs:{href:"javascript:"},on:{click:function(s){t.showReplyInput(e.id,s)}}},[t._v("回复")])]),t._v(" "),i("div",{directives:[{name:"show",rawName:"v-show",value:e.reply_count,expression:"item.reply_count"}],staticClass:"reply-list"},[i("ul",t._l(t.replyObj[e.id]||[],function(e,s){return i("li",{key:s},[i("div",{staticClass:"avatar"},[i("img",{attrs:{src:e.avatar,alt:"头像"}})]),t._v(" "),i("div",{staticClass:"text"},[i("span",{staticClass:"nickname"},[t._v(t._s(e.name))]),t._v(" "),i("p",{staticClass:"message"},[t._v(t._s(e.content))]),t._v(" "),i("span",{staticClass:"time"},[t._v(t._s(e.timestamp))])])])})),t._v(" "),i("a",{directives:[{name:"show",rawName:"v-show",value:!(t.replyArgs[e.id]&&t.replyArgs[e.id].over),expression:"!(replyArgs[item.id]&&replyArgs[item.id].over)"}],attrs:{href:"javascript:"},on:{click:function(s){t.getReply(e.id)}}},[t._v(t._s(t.replyArgs[e.id]&&t.replyArgs[e.id].tip||"查看回复"))])])])])}))]),t._v(" "),i("loading-tip",{attrs:{tip:t.loadingTip}})],1)]),t._v(" "),i("transition",{attrs:{name:"fade"}},[i("toast-tip",{directives:[{name:"show",rawName:"v-show",value:t.activeTips,expression:"activeTips"}],attrs:{content:t.tips}})],1),t._v(" "),i("form",{directives:[{name:"show",rawName:"v-show",value:t.showReply,expression:"showReply"}],staticClass:"form-reply",attrs:{onsubmit:"return false"}},[i("div",{ref:"replyInput",staticClass:"input",attrs:{contenteditable:"true"},on:{input:t.getReplyTxt,blur:t.hideReplyInput}},[t._v("dfsfsd")]),t._v(" "),i("button",{attrs:{type:"submit"},on:{click:t.replyComment}},[t._v("确定")])])],1)},a=[],n={render:i,staticRenderFns:a};e.a=n},"9bRg":function(t,e){},Andp:function(t,e,s){"use strict";function i(t){s("bDDD")}var a=s("k80l"),n=s("bhbE"),r=s("VU/8"),o=i,c=r(a.a,n.a,!1,o,"data-v-7313abfe",null);e.a=c.exports},D4Zv:function(t,e,s){"use strict";var i=s("0jG4");e.a={data:function(){return{surveyDetail:{}}},created:function(){var t=this;i.a.survey.getDetail({id:this.$route.params.id}).then(function(e){t.surveyDetail=e.data.data}).catch(function(t){console.log(t)})},methods:{}}},DxK6:function(t,e,s){"use strict";var i=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{attrs:{id:"app"}},[s("router-view")],1)},a=[],n={render:i,staticRenderFns:a};e.a=n},F9iR:function(t,e,s){"use strict";var i=s("G3hg"),a=s("0QGf"),n=s("VU/8"),r=n(i.a,a.a,!1,null,null,null);e.a=r.exports},G3hg:function(t,e,s){"use strict";var i=s("m3es"),a=s.n(i);e.a={props:{probeType:{type:Number,default:1},click:{type:Boolean,default:!0},scrollX:{type:Boolean,default:!1},listenScroll:{type:Boolean,default:!1},data:{type:Array,default:null},pullup:{type:Boolean,default:!1},pulldown:{type:Boolean,default:!1},beforeScroll:{type:Boolean,default:!1},refreshDelay:{type:Number,default:20}},mounted:function(){var t=this;setTimeout(function(){t._initScroll()},20)},methods:{_initScroll:function(){var t=this;this.$refs.wrapper&&(this.scroll=new a.a(this.$refs.wrapper,{probeType:this.probeType,click:this.click,scrollX:this.scrollX}),this.listenScroll&&this.scroll.on("scroll",function(e){t.$emit("scroll",e)}),this.pullup&&this.scroll.on("scrollEnd",function(){t.scroll.y<=t.scroll.maxScrollY&&t.$emit("scrollToEnd")}),this.pulldown&&this.scroll.on("touchend",function(e){e.y>50&&t.$emit("pulldown")}),this.beforeScroll&&this.scroll.on("beforeScrollStart",function(){t.$emit("beforeScroll")}))},disable:function(){this.scroll&&this.scroll.disable()},enable:function(){this.scroll&&this.scroll.enable()},refresh:function(){this.scroll&&this.scroll.refresh()},scrollTo:function(){this.scroll&&this.scroll.scrollTo.apply(this.scroll,arguments)},scrollToElement:function(){this.scroll&&this.scroll.scrollToElement.apply(this.scroll,arguments)}},watch:{data:function(){var t=this;setTimeout(function(){t.refresh()},this.refreshDelay)}}}},H7Lj:function(t,e){},HN3k:function(t,e,s){"use strict";function i(t){s("5R2O")}var a=s("mcWh"),n=s("TMiH"),r=s("VU/8"),o=i,c=r(a.a,n.a,!1,o,"data-v-5aa6a1a0",null);e.a=c.exports},HTL4:function(t,e,s){"use strict";var i=s("Gu7T"),a=s.n(i),n=s("0jG4"),r=s("F9iR"),o=s("LIpb");e.a={data:function(){return{newsList:[],queryArg:{id:0,limit:10,offset:0,type:this.$route.params.type},pullup:!0,loadingTip:""}},created:function(){this.queryNews()},methods:{queryNews:function(){var t=this;this.queryArg.over||(this.loadingTip="正在加载中",n.a.news.getList(this.queryArg).then(function(e){if(200===e.data.code){var s;(s=t.newsList).push.apply(s,a()(e.data.data.list)),t.queryArg.offset||e.data.data.list.length?e.data.data.list.length<t.queryArg.limit?(t.loadingTip="全部加载完毕",t.queryArg.over=!0):t.loadingTip="释放加载更多":(t.loadingTip="暂无数据",t.queryArg.over=!0)}else console.error(e.data.msg)}).catch(function(e){t.loadingTip="加载失败",console.log(e)}))},getMoreNews:function(){this.queryArg.offset+=this.queryArg.limit,this.queryNews()}},components:{ScrollView:r.a,LoadingTip:o.a}}},IO5o:function(t,e,s){"use strict";var i=s("Gu7T"),a=s.n(i),n=s("F9iR"),r=s("LIpb"),o=s("0jG4");e.a={name:"ProductReporHistory",data:function(){return{queryArg:{id:0,limit:20,offset:0,over:!1},reportList:[],pullup:!0,loadingTip:""}},created:function(){this.queryArg.id=this.$route.params.id,this.queryReport()},methods:{queryReport:function(){var t=this;this.queryArg.over||(this.loadingTip="正在加载中",o.a.product.getHistoryReport(this.queryArg).then(function(e){var s;(s=t.reportList).push.apply(s,a()(e.data.data.list)),t.queryArg.offset||e.data.data.list.length?e.data.data.list.length<t.queryArg.limit?(t.loadingTip="全部加载完毕",t.queryArg.over=!0):t.loadingTip="释放加载更多":(t.loadingTip="暂无数据",t.queryArg.over=!0)}).catch(function(e){t.loadingTip="加载失败",console.log(e)}))},getMoreReport:function(){this.queryArg.offset+=this.queryArg.limit,this.queryReport()}},components:{ScrollView:n.a,LoadingTip:r.a}}},LIpb:function(t,e,s){"use strict";function i(t){s("H7Lj")}var a=s("2Ccr"),n=s("SoWQ"),r=s("VU/8"),o=i,c=r(a.a,n.a,!1,o,"data-v-e0d04a6e",null);e.a=c.exports},M93x:function(t,e,s){"use strict";function i(t){s("Syk4")}var a=s("xJD8"),n=s("DxK6"),r=s("VU/8"),o=i,c=r(a.a,n.a,!1,o,null,null);e.a=c.exports},NHnr:function(t,e,s){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i=s("7+uW"),a=s("M93x"),n=s("YaEn");i.a.config.productionTip=!1,new i.a({el:"#app",router:n.a,template:"<App/>",components:{App:a.a}})},NPyl:function(t,e){},OR2R:function(t,e){},RwXA:function(t,e){},SjSx:function(t,e,s){"use strict";var i=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"survey-detail"},[s("div",{staticClass:"detail-main"},[s("p",{staticClass:"title"},[t._v(t._s(t.surveyDetail.title))]),t._v(" "),s("div",{staticClass:"time-author"},[s("span",[t._v(t._s(t.surveyDetail.time))]),t._v(" "),s("span",[t._v("作者")])]),t._v(" "),s("div",{staticClass:"detail-content"},[t._v(t._s(t.surveyDetail.description))])]),t._v(" "),t._m(0)])},a=[function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("form",{staticClass:"question-form"},[s("ol",{staticClass:"question-list",attrs:{type:"1"}},[s("li",{staticClass:"item",attrs:{"data-type":"radio"}},[s("p",{staticClass:"title",attrs:{"data-index":"1"}},[t._v("标题标题标题标")]),t._v(" "),s("ol",{staticClass:"options",attrs:{type:"A"}},[s("li",[s("input",{attrs:{name:"x",value:"1",type:"radio",id:"a"}}),t._v(" "),s("label",{attrs:{for:"a"}},[t._v("经常")])]),t._v(" "),s("li",[s("input",{attrs:{name:"x",value:"2",type:"radio",id:"b"}}),t._v(" "),s("label",{attrs:{for:"b"}},[t._v("偶尔")])]),t._v(" "),s("li",[s("input",{attrs:{name:"x",value:"3",type:"radio",id:"c"}}),t._v(" "),s("label",{attrs:{for:"c"}},[t._v("从不")])])])]),t._v(" "),s("li",{staticClass:"item",attrs:{"data-type":"multi"}},[s("p",{staticClass:"title",attrs:{"data-index":"2"}},[t._v("标题标题标题标")]),t._v(" "),s("ol",{staticClass:"options",attrs:{type:"A"}},[s("li",[s("input",{attrs:{name:"y",value:"1",type:"checkbox",id:"e"}}),t._v(" "),s("label",{attrs:{for:"e"}},[t._v("中国")])]),t._v(" "),s("li",[s("input",{attrs:{name:"y",value:"2",type:"checkbox",id:"f"}}),t._v(" "),s("label",{attrs:{for:"f"}},[t._v("美国")])]),t._v(" "),s("li",[s("input",{attrs:{name:"y",value:"3",type:"checkbox",id:"g"}}),t._v(" "),s("label",{attrs:{for:"g"}},[t._v("英国")])])])]),t._v(" "),s("li",{staticClass:"item",attrs:{"data-type":"short"}},[s("p",{staticClass:"title",attrs:{"data-index":"3"}},[t._v("标题标题标题标")]),t._v(" "),s("textarea",{attrs:{name:"",id:"",cols:"30",rows:"5"}})])]),t._v(" "),s("div",{staticClass:"btn"},[s("button",{attrs:{type:"submit"}},[t._v("提交")])])])}],n={render:i,staticRenderFns:a};e.a=n},SoWQ:function(t,e,s){"use strict";var i=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"loading-wrapper"},[s("span",[t._v(t._s(t.tip))])])},a=[],n={render:i,staticRenderFns:a};e.a=n},Syk4:function(t,e){},TMiH:function(t,e,s){"use strict";var i=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"product-list"},[s("div",{staticClass:"drop-list"},[s("p",{staticClass:"selected",on:{click:function(e){t.showList=!0}}},[s("a",{attrs:{href:"javascript:"}},[t._v(t._s(t.productSelected.title))])]),t._v(" "),s("transition",{attrs:{name:"slide"}},[s("ul",{directives:[{name:"show",rawName:"v-show",value:t.showList,expression:"showList"}],staticClass:"select-list"},[s("li",{class:{active:0===t.productSelected.id}},[s("a",{attrs:{href:"javascript:"},on:{click:function(e){t.getProduct({id:0,title:"全部产品"})}}},[t._v("全部产品")])]),t._v(" "),t._l(t.productName,function(e){return s("li",{key:e.id,class:{active:t.productSelected.id===e.id}},[s("a",{attrs:{href:"javascript:"},on:{click:function(s){t.getProduct(e)}}},[t._v(t._s(e.title))])])})],2)])],1),t._v(" "),s("scroll-view",{staticClass:"product-list-wrapper",attrs:{data:t.productList,pullup:t.pullup},on:{scrollToEnd:t.getMoreProduct}},[s("div",{staticClass:"scroll-content"},[s("ul",{staticClass:"product-list"},t._l(t.productList,function(e){return s("li",{key:e.id,staticClass:"item"},[s("router-link",{attrs:{to:"/product/"+e.id+"/detail"}},[s("div",{staticClass:"top"},[s("div",{staticClass:"left"},[s("span",[t._v("产品名称：")]),t._v(" "),s("span",{staticClass:"title"},[t._v(t._s(e.title))])]),t._v(" "),s("div",{staticClass:"right"},[t._v(t._s(e.time))])]),t._v(" "),s("div",{staticClass:"bottom"},[s("span",[t._v("投资金额：")]),t._v(" "),s("span",{staticClass:"currency"},[t._v(t._s(e.money))])])])],1)})),t._v(" "),s("loading-tip",{attrs:{tip:t.loadingTip}})],1)]),t._v(" "),s("transition",{attrs:{name:"fade"}},[s("div",{directives:[{name:"show",rawName:"v-show",value:t.showList,expression:"showList"}],staticClass:"mask",on:{click:function(e){t.showList=!1}}})])],1)},a=[],n={render:i,staticRenderFns:a};e.a=n},TcHr:function(t,e,s){"use strict";var i=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"product-detail hide"},[s("div",{staticClass:"header"},[s("div",{staticClass:"invest-sum"},[s("span",[t._v("投资金额：")]),t._v(" "),s("span",{staticClass:"currency"},[t._v(t._s(t.productDetail.money))])]),t._v(" "),s("router-link",{staticClass:"link",attrs:{to:"/product/"+t.productDetail.id+"/report/history"}},[t._v("查看报告")])],1),t._v(" "),s("div",{staticClass:"section"},[s("ul",[s("li",[s("span",[t._v("产品状态：")]),t._v(" "),s("span",[t._v(t._s(t.status))])]),t._v(" "),s("li",[s("span",[t._v("成立日期：")]),t._v(" "),s("span",[t._v(t._s(t.productDetail.establish))])]),t._v(" "),s("li",[s("span",[t._v("管理人：")]),t._v(" "),s("span",[t._v(t._s(t.productDetail.manager))])]),t._v(" "),s("li",[s("span",[t._v("产品期限：")]),t._v(" "),s("span",[t._v(t._s(t.productDetail.deadline))])]),t._v(" "),s("li",[s("span",[t._v("管理机构：")]),t._v(" "),s("span",[t._v(t._s(t.productDetail.trusteeship))])]),t._v(" "),s("li",[s("span",[t._v("投资范围属性：")]),t._v(" "),s("span",[t._v(t._s(t.productDetail.scope))])])])]),t._v(" "),s("div",{staticClass:"section"},[s("ul",[s("li",[s("span",[t._v("认购费：")]),t._v(" "),s("span",{staticClass:"currency"},[t._v(t._s(t.productDetail.subscription_fee))])]),t._v(" "),s("li",[s("span",[t._v("赎回费：")]),t._v(" "),s("span",{staticClass:"currency"},[t._v(t._s(t.productDetail.redemption_fee))])]),t._v(" "),s("li",[s("span",[t._v("管理费：")]),t._v(" "),s("span",{staticClass:"currency"},[t._v(t._s(t.productDetail.management_fee))])]),t._v(" "),s("li",[s("span",[t._v("托管费：")]),t._v(" "),s("span",{staticClass:"currency"},[t._v(t._s(t.productDetail.trust_fee))])]),t._v(" "),s("li",[s("span",[t._v("外包服务费：")]),t._v(" "),s("span",{staticClass:"currency"},[t._v(t._s(t.productDetail.outsourcing_fee))])])])])])},a=[],n={render:i,staticRenderFns:a};e.a=n},UWmW:function(t,e,s){"use strict";var i=s("0jG4");e.a={name:"ProductDetail",data:function(){return{productDetail:{}}},created:function(){var t=this;i.a.product.getDetail({id:this.$route.params.id}).then(function(e){200===e.data.code&&(t.productDetail=e.data.data.info)}).catch(function(t){console.log(t)})},computed:{status:function(){switch(Number(this.productDetail.status)){case 1:return"募集";case 2:return"存续";case 3:return"退出"}}}}},VPwr:function(t,e,s){"use strict";var i=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{ref:"wrapper",staticClass:"company-profile"},[s("div",{staticClass:"content",domProps:{innerHTML:t._s(t.companyProfile)}})])},a=[],n={render:i,staticRenderFns:a};e.a=n},VqWC:function(t,e,s){"use strict";var i=s("Gu7T"),a=s.n(i),n=s("0jG4"),r=s("F9iR");e.a={data:function(){return{surveyArg:{limit:10,offset:0,over:!1},surveyList:[],pullup:!0}},created:function(){this.querySurvey()},computed:{},methods:{querySurvey:function(){var t=this;if(this.querySurvey.over)return void alert("over");n.a.survey.getList(this.surveyArg).then(function(e){var s;(s=t.surveyList).push.apply(s,a()(e.data.data)),t.surveyList.limit>t.surveyList.length&&(t.surveyArg.over=!0)})},getMoreSurvey:function(){this.querySurvey.offset+=10,this.querySurvey()}},filters:{status:function(t){switch(Number(t)){case 1:return"已结束";case 2:return"已投票";case 3:return"暂停"}}},components:{ScrollView:r.a}}},VypJ:function(t,e,s){"use strict";function i(t){s("RwXA")}var a=s("UWmW"),n=s("TcHr"),r=s("VU/8"),o=i,c=r(a.a,n.a,!1,o,"data-v-36f34553",null);e.a=c.exports},Xfx8:function(t,e,s){"use strict";var i=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("scroll-view",{staticClass:"scroll-wrapper",attrs:{data:t.newsList,pullup:t.pullup},on:{scrollToEnd:t.getMoreNews}},[s("div",{staticClass:"scroll-content"},[s("ul",{staticClass:"news-list"},t._l(t.newsList,function(e){return s("li",{key:e.id,staticClass:"item"},[s("router-link",{attrs:{to:"/news/"+e.id+"/detail"}},[s("div",{staticClass:"text"},[s("p",{staticClass:"title"},[t._v(t._s(e.title))]),t._v(" "),s("span",{staticClass:"time"},[t._v(t._s(e.timestamp))])]),t._v(" "),s("div",{staticClass:"cover"},[s("img",{attrs:{src:e.imgurl,alt:"cover"}})])])],1)})),t._v(" "),s("loading-tip",{attrs:{tip:t.loadingTip}})],1)])},a=[],n={render:i,staticRenderFns:a};e.a=n},YaEn:function(t,e,s){"use strict";function i(t,e,s){var i=t.params.aid,a=encodeURI(window.location.href);o.a.post("/weixin/user/isLogin").then(function(t){402===t.data.code?o.a.post("/weixin/user/login/aid/"+i+"/callback/"+a).then(function(t){}).catch(function(t){console.error(t)}):200===t.data.code&&s()}).catch(function(t){console.error(t)})}var a=s("7+uW"),n=s("/ocq"),r=s("mtWM"),o=s.n(r),c=s("7RoA"),l=s("HN3k"),u=s("VypJ"),p=s("zgsS"),d=s("jYKV"),v=s("yFxT"),f=s("mWUr"),m=s("mcZf"),h=s("y0Ls");a.a.use(n.a);e.a=new n.a({routes:[{path:"/company/aid/:aid",name:"Company",component:c.a,beforeEnter:function(t,e,s){i(t,e,s)}},{path:"/product/list/aid/:aid",name:"Product",component:l.a,beforeEnter:function(t,e,s){i(t,e,s)}},{path:"/product/:id/detail/aid/:aid",component:u.a,beforeEnter:function(t,e,s){i(t,e,s)}},{path:"/product/:id/report/history/aid/:aid",component:p.a,beforeEnter:function(t,e,s){i(t,e,s)}},{path:"/news/list/:type/aid/:aid",component:m.a,beforeEnter:function(t,e,s){i(t,e,s)}},{path:"/news/:id/detail/aid/:aid",component:h.a,beforeEnter:function(t,e,s){i(t,e,s)}},{path:"/survey/list/aid/:aid",component:d.a,beforeEnter:function(t,e,s){i(t,e,s)}},{path:"/survey/:id/detail/aid/:aid",component:v.a,beforeEnter:function(t,e,s){i(t,e,s)}},{path:"/survey/:id/tips/aid/:aid",component:f.a,beforeEnter:function(t,e,s){i(t,e,s)}}]})},bDDD:function(t,e){},bhbE:function(t,e,s){"use strict";var i=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"wrapper"},[s("div",{staticClass:"content"},[t._v(t._s(t.content))])])},a=[],n={render:i,staticRenderFns:a};e.a=n},fNuo:function(t,e,s){"use strict";var i=s("0jG4"),a=s("m3es"),n=s.n(a);e.a={name:"Company",data:function(){return{companyProfile:""}},created:function(){var t=this;i.a.company.getContent().then(function(e){200===e.data.code?(t.companyProfile=e.data.data.content,t.$nextTick(function(){t.scroll=new n.a(t.$refs.wrapper,{click:!0})})):console.error(e.data.msg)}).catch(function(t){console.error(t)})}}},gkqr:function(t,e,s){"use strict";var i=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"report-history"},[t._m(0),t._v(" "),s("scroll-view",{staticClass:"report-list-wrapper",attrs:{data:t.reportList,pullup:t.pullup},on:{scrollToEnd:t.getMoreReport}},[s("div",{staticClass:"scroll-content"},[s("ul",{staticClass:"report-list"},t._l(t.reportList,function(e){return s("li",{key:e.file_id,staticClass:"item"},[s("span",[t._v(t._s(e.time))]),t._v(" "),s("a",{attrs:{href:e.url,download:e.time+"."+e.ext}},[t._v("下载报告")])])})),t._v(" "),s("loading-tip",{attrs:{tip:t.loadingTip}})],1)])],1)},a=[function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("div",{staticClass:"header"},[s("span",[t._v("发布日期")]),t._v(" "),s("span",[t._v("操作")])])}],n={render:i,staticRenderFns:a};e.a=n},jYKV:function(t,e,s){"use strict";function i(t){s("OR2R")}var a=s("VqWC"),n=s("td+4"),r=s("VU/8"),o=i,c=r(a.a,n.a,!1,o,"data-v-21ae9080",null);e.a=c.exports},k80l:function(t,e,s){"use strict";e.a={props:{content:{type:String,default:"提示"}}}},mWUr:function(t,e,s){"use strict";var i=s("VU/8"),a=i(null,null,!1,null,null,null);e.a=a.exports},mcWh:function(t,e,s){"use strict";var i=s("Gu7T"),a=s.n(i),n=s("0jG4"),r=s("F9iR"),o=s("LIpb");e.a={name:"ProductList",data:function(){return{productList:[],productName:[],productSelected:{id:0,title:"全部产品"},queryArg:{id:0,limit:10,offset:0},showList:!1,pullup:!0,loadingTip:""}},created:function(){var t=this;n.a.product.getName().then(function(e){t.productName=e.data.data.list}).catch(function(t){console.log(t)}),this.queryProduct()},mounted:function(){},computed:{},methods:{queryProduct:function(){var t=this;this.queryArg.over||(this.loadingTip="正在加载中",n.a.product.getList(this.queryArg).then(function(e){var s;(s=t.productList).push.apply(s,a()(e.data.data.list)),t.queryArg.offset||e.data.data.list.length?e.data.data.list.length<t.queryArg.limit?(t.loadingTip="全部加载完毕",t.queryArg.over=!0):t.loadingTip="释放加载更多":(t.loadingTip="暂无数据",t.queryArg.over=!0)}).catch(function(e){t.loadingTip="加载失败",console.log(e)}))},getProduct:function(t){t.id!==this.productSelected.id&&(this.showList=!1,this.productSelected=t,this.queryArg={id:t.id,offset:0,limit:10},this.loadingTip="",this.productList=[],this.queryProduct())},getMoreProduct:function(){this.queryArg.offset+=this.queryArg.limit,this.queryProduct()}},components:{ScrollView:r.a,LoadingTip:o.a}}},mcZf:function(t,e,s){"use strict";function i(t){s("9bRg")}var a=s("HTL4"),n=s("Xfx8"),r=s("VU/8"),o=i,c=r(a.a,n.a,!1,o,"data-v-abf71a40",null);e.a=c.exports},oLZf:function(t,e,s){"use strict";var i=s("Gu7T"),a=s.n(i),n=s("0jG4"),r=s("F9iR"),o=s("LIpb"),c=s("Andp");e.a={data:function(){return{isAdmin:!0,pullup:!0,commentObj:{avatar:"",name:"",content:"",timestamp:"刚刚"},newsDetail:{},timeHandle:0,tips:"",activeTips:!1,showLeaveMsg:!1,msgContent:"",commentsArg:{offset:0,limit:10,id:this.$route.params.id},commentsList:[],loadingTip:"",showReply:!1,replyMsg:{news_id:this.$route.params.id,comment_id:0,content:""},replyObj:{},replyArgs:{}}},created:function(){var t=this;n.a.news.getDetail({id:this.$route.params.id}).then(function(e){200===e.data.code?(t.newsDetail=e.data.data.detail,t.commentObj.avatar=e.data.data.detail.avatar,t.commentObj.name=e.data.data.detail.name):t.showTips(e.data.msg)}).catch(function(t){console.err(t)}),this.queryComments()},methods:{hideInput:function(){this.$refs.textarea.innerText||(this.showLeaveMsg=!1)},showInput:function(){this.showLeaveMsg=!0;var t=this;setTimeout(function(){t.$refs.textarea.focus()},100)},getText:function(){var t=this;clearTimeout(this.timeHandle),this.timeHandle=setTimeout(function(){t.msgContent=t.$refs.textarea.innerText},200)},sendMsg:function(t){var e=this;this.msgContent.trim().length>3||t._constructed?n.a.news.sendMsg({news_id:this.$route.params.id,content:this.msgContent}).then(function(t){200===t.data.code&&(e.showLeaveMsg=!1,e.$refs.textarea.innerText="",e.showTips("评论已发送"),e.commentObj.content=e.msgContent,e.commentObj.id=t.data.data.id,t.data.data.id&&e.commentsList.unshift(e.commentObj))}).catch(function(t){console.err(t)}):this.showTips("评论内容至少3个字评论内容至少3个字评论内容至少3个字评论内容至少3个字")},showReplyInput:function(t,e){var s=this;e._constructed&&(this.showReply=!0,this.replyMsg.comment_id=t,setTimeout(function(){s.$refs.replyInput.focus()},100))},getReplyTxt:function(){var t=this;clearTimeout(this.timeHandle),this.timeHandle=setTimeout(function(){t.replyMsg.content=t.$refs.replyInput.innerText},200)},replyComment:function(){var t=this;n.a.news.replyComment(this.replyMsg).then(function(e){200===e.data.code&&(t.showReply=!1,t.$refs.replyInput.innerText="",t.showTips("回复已发送"))}).catch(function(t){console.error(t)})},getReply:function(t){var e=this;if(this.replyArgs[t]){if(this.replyArgs[t].over)return;this.replyArgs[t].offset+=this.replyArgs[t].limit}else this.replyArgs[t]={id:t,offset:0,limit:5};n.a.news.getReply(this.replyArgs[t]).then(function(s){if(200===s.data.code){if(e.replyObj[t]){var i;(i=e.replyObj[t]).push.apply(i,a()(s.data.data.list))}else e.$set(e.replyObj,t,s.data.data.list);s.data.data.list.length===e.replyArgs[t].limit?e.replyArgs[t].tip="加载更多评论":e.replyArgs[t].over=!0}}).catch(function(t){console.error(t)})},hideReplyInput:function(){this.$refs.replyInput.innerText||(this.showReply=!1)},showTips:function(t){this.tips=t,this.activeTips=!0;var e=this;setTimeout(function(){e.activeTips=!1},1500)},queryComments:function(){var t=this;this.commentsArg.over||(this.loadingTip="正在加载中",n.a.news.getComments(this.commentsArg).then(function(e){if(200===e.data.code){var s;(s=t.commentsList).push.apply(s,a()(e.data.data.list)),t.commentsArg.offset||e.data.data.list.length?e.data.data.list.length<t.commentsArg.limit?(t.loadingTip="全部加载完毕",t.commentsArg.over=!0):t.loadingTip="释放加载更多":(t.loadingTip="暂无数据",t.commentsArg.over=!0)}else t.loadingTip="加载失败",t.showTips(e.data.msg)}).catch(function(t){console.error(t)}))},getMoreComments:function(){this.commentsArg.offset+=this.commentsArg.limit,this.queryComments()}},components:{ScrollView:r.a,LoadingTip:o.a,ToastTip:c.a}}},rf9I:function(t,e){},tCTf:function(t,e){t.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAoCAMAAAChHKjRAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTM4IDc5LjE1OTgyNCwgMjAxNi8wOS8xNC0wMTowOTowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTcgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjM4NkJERDA2QzEyODExRTc5ODM4RjI0RTE4MDUxNjU4IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjM4NkJERDA3QzEyODExRTc5ODM4RjI0RTE4MDUxNjU4Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6Mzg2QkREMDRDMTI4MTFFNzk4MzhGMjRFMTgwNTE2NTgiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6Mzg2QkREMDVDMTI4MTFFNzk4MzhGMjRFMTgwNTE2NTgiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6hCtbpAAAABlBMVEW0JjIAAAAwnwIiAAAAAnRSTlP/AOW3MEoAAACCSURBVHjaxNRLCgAhDAPQ5P6XHsYZv7RJd3Yl+NCmiGBawFgJMxSU6QrS/ArafMqd1JTp6VNIsy8KeXaFln7T67ZUcePYszMaAXAqGoQQ4awAoYByM1FOJlKmI2mIgiEKpiFnXuRIhFhALKD4vRfMgVhA9Ej8L/SGau8aMnUDPQIMAOmkA45dOlS5AAAAAElFTkSuQmCC"},"td+4":function(t,e,s){"use strict";var i=function(){var t=this,e=t.$createElement,s=t._self._c||e;return s("scroll-view",{staticClass:"survey-list-wrapper",attrs:{data:t.surveyList,pullup:t.pullup},on:{pullup:t.getMoreSurvey}},[s("ul",{staticClass:"survey-list"},t._l(t.surveyList,function(e){return s("li",{key:e.id,staticClass:"item"},[s("router-link",{attrs:{to:"/survey/"+e.id+"/detail"}},[s("div",{staticClass:"cover"},[s("img",{attrs:{src:"",alt:"封面"}})]),t._v(" "),s("div",{staticClass:"brief"},[s("span",{staticClass:"title"},[t._v(t._s(e.title))]),t._v(" "),s("span",{staticClass:"author"},[t._v(t._s(e.author))])]),t._v(" "),s("div",{staticClass:"stamp"},[s("span",{staticClass:"time"},[t._v(t._s(e.time))]),t._v(" "),s("span",{staticClass:"status",attrs:{status:e.status}},[t._v(t._s(t._f("status")(e.status)))])])])],1)}))])},a=[],n={render:i,staticRenderFns:a};e.a=n},xJD8:function(t,e,s){"use strict";e.a={name:"app",created:function(){}}},y0Ls:function(t,e,s){"use strict";function i(t){s("24iT")}var a=s("oLZf"),n=s("7Z49"),r=s("VU/8"),o=i,c=r(a.a,n.a,!1,o,"data-v-2621c693",null);e.a=c.exports},yFxT:function(t,e,s){"use strict";function i(t){s("rf9I")}var a=s("D4Zv"),n=s("SjSx"),r=s("VU/8"),o=i,c=r(a.a,n.a,!1,o,"data-v-5bbafc33",null);e.a=c.exports},zgsS:function(t,e,s){"use strict";function i(t){s("2gjM")}var a=s("IO5o"),n=s("gkqr"),r=s("VU/8"),o=i,c=r(a.a,n.a,!1,o,"data-v-5a98ed36",null);e.a=c.exports}},["NHnr"]);
//# sourceMappingURL=app.15aca678dcb16ff4134f.js.map