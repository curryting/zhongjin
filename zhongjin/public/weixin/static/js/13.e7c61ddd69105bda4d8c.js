webpackJsonp([13],{"0jG4":function(t,e,n){"use strict";var i=n("mtWM"),a=n.n(i),r=/\/aid\/([0-9]+)(\/|$)/.exec(window.location.href),o=r?r[1]:"00000000",s="/aid/"+o;e.a={company:{getContent:function(t){return a.a.post("/weixin/profile/index"+s,t)}},product:{getName:function(t){return a.a.post("/weixin/product/lists"+s,t)},getList:function(t){return a.a.post("/weixin/product/investLists"+s,t)},getDetail:function(t){return a.a.post("/weixin/product/detail"+s,t)},getReport:function(t){return a.a.post("xxxx",t)},getHistoryReport:function(t){return a.a.post("/weixin/product/reportLists"+s,t)}},news:{getList:function(){return"/weixin/news/lists"+s},getDetail:function(){return["/weixin/news/previewDetail"+s,"/weixin/news/detail"+s]},sendMsg:function(t){return"/weixin/news/comment"+s},replyComment:function(t){return"/weixin/news/reply"+s},getReply:function(t){return"/weixin/news/readReply"+s},getComments:function(t){return"/weixin/news/readComment"+s}},notice:{getList:function(){return"/weixin/notice/lists"+s},getDetail:function(){return["/weixin/notice/previewDetail"+s,"/weixin/notice/detail"+s]},sendMsg:function(){return"/weixin/notice/comment"+s},replyComment:function(){return"/weixin/notice/reply"+s},getReply:function(){return"/weixin/notice/readReply"+s},getComments:function(){return"/weixin/notice/readComment"+s}},vote:{getList:function(){return"/weixin/vote/lists"+s},getDetail:function(t){return a.a.post("/weixin/vote/detail"+s,t)},statistic:function(t){return a.a.post("/weixin/vote/statis"+s,t)},hadJoin:function(t){return"/weixin/vote/getJoinMem"+s},notJoin:function(t){return"/weixin/vote/getUnjoinMem"+s},submit:function(t){return a.a.post("/weixin/vote/answer"+s,t)},remind:function(t){return a.a.post("/weixin/vote/remind"+s,t)}},survey:{getList:function(){return"/weixin/survey/lists"+s},getDetail:function(t){return a.a.post("/weixin/survey/detail"+s,t)},submit:function(t){return a.a.post("/weixin/survey/answer"+s,t)},hadJoin:function(t){return"/weixin/survey/getJoinMem"+s},notJoin:function(t){return"/weixin/survey/getUnjoinMem"+s},remind:function(t){return a.a.post("/weixin/survey/remind"+s,t)}}}},RwXA:function(t,e,n){var i=n("cGBR");"string"==typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);n("rjj0")("85868ad6",i,!0)},TcHr:function(t,e,n){"use strict";var i=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"product-detail hide"},[n("div",{staticClass:"header"},[n("div",{staticClass:"invest-sum"},[n("span",[t._v("投资金额：")]),t._v(" "),n("span",{staticClass:"currency"},[t._v(t._s(t.productDetail.money))])]),t._v(" "),n("router-link",{staticClass:"link",attrs:{to:"/product/"+t.productDetail.id+"/report/history/aid/"+t.aid}},[t._v("查看报告")])],1),t._v(" "),n("div",{staticClass:"section"},[n("ul",[n("li",[n("span",[t._v("产品状态：")]),t._v(" "),n("span",[t._v(t._s(t.status))])]),t._v(" "),n("li",[n("span",[t._v("成立日期：")]),t._v(" "),n("span",[t._v(t._s(t.productDetail.establish))])]),t._v(" "),n("li",[n("span",[t._v("管理人：")]),t._v(" "),n("span",[t._v(t._s(t.productDetail.manager))])]),t._v(" "),n("li",[n("span",[t._v("产品期限：")]),t._v(" "),n("span",[t._v(t._s(t.productDetail.deadline))])]),t._v(" "),n("li",[n("span",[t._v("管理机构：")]),t._v(" "),n("span",[t._v(t._s(t.productDetail.trusteeship))])]),t._v(" "),n("li",[n("span",[t._v("投资范围属性：")]),t._v(" "),n("span",[t._v(t._s(t.productDetail.scope))])])])]),t._v(" "),n("div",{staticClass:"section"},[n("ul",[n("li",[n("span",[t._v("认购费：")]),t._v(" "),n("span",{staticClass:"currency"},[t._v(t._s(t.productDetail.subscription_fee))])]),t._v(" "),n("li",[n("span",[t._v("赎回费：")]),t._v(" "),n("span",{staticClass:"currency"},[t._v(t._s(t.productDetail.redemption_fee))])]),t._v(" "),n("li",[n("span",[t._v("管理费：")]),t._v(" "),n("span",{staticClass:"currency"},[t._v(t._s(t.productDetail.management_fee))])]),t._v(" "),n("li",[n("span",[t._v("托管费：")]),t._v(" "),n("span",{staticClass:"currency"},[t._v(t._s(t.productDetail.trust_fee))])]),t._v(" "),n("li",[n("span",[t._v("外包服务费：")]),t._v(" "),n("span",{staticClass:"currency"},[t._v(t._s(t.productDetail.outsourcing_fee))])])])])])},a=[],r={render:i,staticRenderFns:a};e.a=r},UWmW:function(t,e,n){"use strict";var i=n("0jG4");e.a={name:"ProductDetail",data:function(){return{productDetail:{},aid:this.$route.params.aid}},created:function(){var t=this;i.a.product.getDetail({id:this.$route.params.id}).then(function(e){200===e.data.code&&(t.productDetail=e.data.data.info)}).catch(function(t){console.log(t)})},computed:{status:function(){switch(Number(this.productDetail.status)){case 1:return"募集";case 2:return"存续";case 3:return"退出"}}}}},VypJ:function(t,e,n){"use strict";function i(t){n("RwXA")}Object.defineProperty(e,"__esModule",{value:!0});var a=n("UWmW"),r=n("TcHr"),o=n("VU/8"),s=i,d=o(a.a,r.a,!1,s,"data-v-36f34553",null);e.default=d.exports},cGBR:function(t,e,n){e=t.exports=n("FZ+f")(!0),e.push([t.i,"body[data-v-36f34553],html[data-v-36f34553]{line-height:1;font-weight:200;font-family:PingFang SC,STHeitSC-Light,Helvetica-Light,arial,sans-serif}a[data-v-36f34553]{text-decoration:none;color:inherit}textarea[readonly][data-v-36f34553]{outline:none}.product-detail .header[data-v-36f34553]{display:-webkit-box;display:-ms-flexbox;display:flex;-webkit-box-pack:justify;-ms-flex-pack:justify;justify-content:space-between;height:.8rem;padding:0 .3rem;margin:.26rem 0 .17rem;background-color:#fff;-webkit-box-shadow:0 3px 5px 1px #ddd;box-shadow:0 3px 5px 1px #ddd;line-height:.8rem;font-size:.34rem}.product-detail .header .invest-sum .currency[data-v-36f34553]{color:#b42632}.product-detail .header .link[data-v-36f34553]{text-decoration:underline;font-size:.3rem;color:#3d8ae6}.product-detail .section[data-v-36f34553]{padding:.25rem;margin-bottom:.17rem;background-color:#fff;-webkit-box-shadow:0 3px 5px 1px #ddd;box-shadow:0 3px 5px 1px #ddd}.product-detail .section ul>li[data-v-36f34553]{display:-webkit-box;display:-ms-flexbox;display:flex;margin-bottom:.3rem}.product-detail .section ul>li[data-v-36f34553]:last-child{margin-bottom:0}.product-detail .section ul>li span[data-v-36f34553]{font-size:.32rem;word-break:break-all}.product-detail .section ul>li span[data-v-36f34553]:first-child{-ms-flex-negative:0;flex-shrink:0;-ms-flex-preferred-size:2.5rem;flex-basis:2.5rem;text-align:right}","",{version:3,sources:["C:/Users/Administrator/Desktop/workspace/code/vue/zj/src/components/product/ProductDetail.vue"],names:[],mappings:"AACA,4CAEE,cAAe,AACf,gBAAiB,AACjB,uEAAmF,CACpF,AACD,mBACE,qBAAsB,AACtB,aAAe,CAChB,AACD,oCACE,YAAc,CACf,AACD,yCACE,oBAAqB,AACrB,oBAAqB,AACrB,aAAc,AACd,yBAA0B,AACtB,sBAAuB,AACnB,8BAA+B,AACvC,aAAe,AACf,gBAAkB,AAClB,uBAA4B,AAC5B,sBAAuB,AACvB,sCAAuC,AAC/B,8BAA+B,AACvC,kBAAoB,AACpB,gBAAmB,CACpB,AACD,+DACE,aAAe,CAChB,AACD,+CACE,0BAA2B,AAC3B,gBAAkB,AAClB,aAAe,CAChB,AACD,0CACE,eAAiB,AACjB,qBAAuB,AACvB,sBAAuB,AACvB,sCAAuC,AAC/B,6BAA+B,CACxC,AACD,gDACE,oBAAqB,AACrB,oBAAqB,AACrB,aAAc,AACd,mBAAsB,CACvB,AACD,2DACE,eAAiB,CAClB,AACD,qDACE,iBAAmB,AACnB,oBAAsB,CACvB,AACD,iEACE,oBAAqB,AACjB,cAAe,AACnB,+BAAgC,AAC5B,kBAAmB,AACvB,gBAAkB,CACnB",file:"ProductDetail.vue",sourcesContent:['\nbody[data-v-36f34553],\nhtml[data-v-36f34553] {\n  line-height: 1;\n  font-weight: 200;\n  font-family: "PingFang SC", "STHeitSC-Light", "Helvetica-Light", arial, sans-serif;\n}\na[data-v-36f34553] {\n  text-decoration: none;\n  color: inherit;\n}\ntextarea[readonly][data-v-36f34553] {\n  outline: none;\n}\n.product-detail .header[data-v-36f34553] {\n  display: -webkit-box;\n  display: -ms-flexbox;\n  display: flex;\n  -webkit-box-pack: justify;\n      -ms-flex-pack: justify;\n          justify-content: space-between;\n  height: 0.8rem;\n  padding: 0 0.3rem;\n  margin: 0.26rem 0 0.17rem 0;\n  background-color: #fff;\n  -webkit-box-shadow: 0 3px 5px 1px #ddd;\n          box-shadow: 0 3px 5px 1px #ddd;\n  line-height: 0.8rem;\n  font-size: 0.34rem;\n}\n.product-detail .header .invest-sum .currency[data-v-36f34553] {\n  color: #b42632;\n}\n.product-detail .header .link[data-v-36f34553] {\n  text-decoration: underline;\n  font-size: 0.3rem;\n  color: #3d8ae6;\n}\n.product-detail .section[data-v-36f34553] {\n  padding: 0.25rem;\n  margin-bottom: 0.17rem;\n  background-color: #fff;\n  -webkit-box-shadow: 0 3px 5px 1px #ddd;\n          box-shadow: 0 3px 5px 1px #ddd;\n}\n.product-detail .section ul>li[data-v-36f34553] {\n  display: -webkit-box;\n  display: -ms-flexbox;\n  display: flex;\n  margin-bottom: 0.3rem;\n}\n.product-detail .section ul>li[data-v-36f34553]:last-child {\n  margin-bottom: 0;\n}\n.product-detail .section ul>li span[data-v-36f34553] {\n  font-size: 0.32rem;\n  word-break: break-all;\n}\n.product-detail .section ul>li span[data-v-36f34553]:first-child {\n  -ms-flex-negative: 0;\n      flex-shrink: 0;\n  -ms-flex-preferred-size: 2.5rem;\n      flex-basis: 2.5rem;\n  text-align: right;\n}'],sourceRoot:""}])}});
//# sourceMappingURL=13.e7c61ddd69105bda4d8c.js.map