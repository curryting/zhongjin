webpackJsonp([14],{"0jG4":function(n,t,e){"use strict";var i=e("mtWM"),o=e.n(i),r=/\/aid\/([0-9]+)(\/|$)/.exec(window.location.href),a=r?r[1]:"00000000",s="/aid/"+a;t.a={company:{getContent:function(n){return o.a.post("/weixin/profile/index"+s,n)}},product:{getName:function(n){return o.a.post("/weixin/product/lists"+s,n)},getList:function(n){return o.a.post("/weixin/product/investLists"+s,n)},getDetail:function(n){return o.a.post("/weixin/product/detail"+s,n)},getReport:function(n){return o.a.post("xxxx",n)},getHistoryReport:function(n){return o.a.post("/weixin/product/reportLists"+s,n)}},news:{getList:function(){return"/weixin/news/lists"+s},getDetail:function(){return["/weixin/news/previewDetail"+s,"/weixin/news/detail"+s]},sendMsg:function(n){return"/weixin/news/comment"+s},replyComment:function(n){return"/weixin/news/reply"+s},getReply:function(n){return"/weixin/news/readReply"+s},getComments:function(n){return"/weixin/news/readComment"+s}},notice:{getList:function(){return"/weixin/notice/lists"+s},getDetail:function(){return["/weixin/notice/previewDetail"+s,"/weixin/notice/detail"+s]},sendMsg:function(){return"/weixin/notice/comment"+s},replyComment:function(){return"/weixin/notice/reply"+s},getReply:function(){return"/weixin/notice/readReply"+s},getComments:function(){return"/weixin/notice/readComment"+s}},vote:{getList:function(){return"/weixin/vote/lists"+s},getDetail:function(n){return o.a.post("/weixin/vote/detail"+s,n)},statistic:function(n){return o.a.post("/weixin/vote/statis"+s,n)},hadJoin:function(n){return"/weixin/vote/getJoinMem"+s},notJoin:function(n){return"/weixin/vote/getUnjoinMem"+s},submit:function(n){return o.a.post("/weixin/vote/answer"+s,n)},remind:function(n){return o.a.post("/weixin/vote/remind"+s,n)}},survey:{getList:function(){return"/weixin/survey/lists"+s},getDetail:function(n){return o.a.post("/weixin/survey/detail"+s,n)},submit:function(n){return o.a.post("/weixin/survey/answer"+s,n)},hadJoin:function(n){return"/weixin/survey/getJoinMem"+s},notJoin:function(n){return"/weixin/survey/getUnjoinMem"+s},remind:function(n){return o.a.post("/weixin/survey/remind"+s,n)}}}},"7RoA":function(n,t,e){"use strict";function i(n){e("NPyl")}Object.defineProperty(t,"__esModule",{value:!0});var o=e("fNuo"),r=e("VPwr"),a=e("VU/8"),s=i,c=a(o.a,r.a,!1,s,"data-v-c7527804",null);t.default=c.exports},NPyl:function(n,t,e){var i=e("thxs");"string"==typeof i&&(i=[[n.i,i,""]]),i.locals&&(n.exports=i.locals);e("rjj0")("5f0aba51",i,!0)},VPwr:function(n,t,e){"use strict";var i=function(){var n=this,t=n.$createElement,e=n._self._c||t;return e("div",{staticClass:"company-profile"},[e("div",{staticClass:"content",domProps:{innerHTML:n._s(n.companyProfile)}})])},o=[],r={render:i,staticRenderFns:o};t.a=r},fNuo:function(n,t,e){"use strict";var i=e("0jG4");t.a={name:"Company",data:function(){return{companyProfile:""}},created:function(){var n=this;i.a.company.getContent().then(function(t){200===t.data.code?n.companyProfile=t.data.data.content:console.error(t.data.msg)}).catch(function(n){console.error(n)})}}},thxs:function(n,t,e){t=n.exports=e("FZ+f")(!0),t.push([n.i,".company-profile[data-v-c7527804]{position:absolute;top:0;bottom:0;left:0;right:0}.company-profile[data-v-c7527804]::-webkit-scrollbar{display:none}.company-profile .content[data-v-c7527804]{padding:.2rem;text-align:justify;font-size:.34rem}.company-profile .content h1[data-v-c7527804]{font-size:.48rem}","",{version:3,sources:["C:/Users/Administrator/Desktop/workspace/code/vue/zj/src/components/company/Company.vue"],names:[],mappings:"AACA,kCACE,kBAAmB,AACnB,MAAO,AACP,SAAU,AACV,OAAQ,AACR,OAAS,CACV,AACD,qDACE,YAAc,CACf,AACD,2CACE,cAAgB,AAChB,mBAAoB,AACpB,gBAAmB,CACpB,AACD,8CACE,gBAAmB,CACpB",file:"Company.vue",sourcesContent:["\n.company-profile[data-v-c7527804] {\n  position: absolute;\n  top: 0;\n  bottom: 0;\n  left: 0;\n  right: 0;\n}\n.company-profile[data-v-c7527804]::-webkit-scrollbar {\n  display: none;\n}\n.company-profile .content[data-v-c7527804] {\n  padding: 0.2rem;\n  text-align: justify;\n  font-size: 0.34rem;\n}\n.company-profile .content h1[data-v-c7527804] {\n  font-size: 0.48rem;\n}"],sourceRoot:""}])}});
//# sourceMappingURL=14.c8472a40b570e358d2c5.js.map