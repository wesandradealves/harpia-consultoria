import n from"./AdditionalInformation.fc0df632.js";import p from"./Category.6e7bd4e1.js";import s from"./Features.c41a3369.js";import a from"./Import.2d43f5ad.js";import c from"./LicenseKey.be1c841d.js";import l from"./SearchAppearance.c48f14f4.js";import u from"./SmartRecommendations.dec14ea6.js";import d from"./Success.144ae0df.js";import f from"./Welcome.1922f436.js";import{c as e,m,d as _}from"./vuex.esm-bundler.f966fce5.js";import{_ as h,j as S,l as g,o as y}from"./_plugin-vue_export-helper.299eda55.js";import"./default-i18n.0e8bc810.js";import"./_commonjsHelpers.f84db168.js";import"./Wizard.b09ab7d7.js";import"./WpTable.43babaf5.js";import"./index.f123d27f.js";import"./Caret.e5d23aaa.js";import"./Index.c0b708e6.js";import"./Row.0ab5735c.js";import"./helpers.73050afe.js";import"./RequiresUpdate.fe231e49.js";import"./postContent.42ceb47d.js";import"./index.c7acbe5b.js";import"./cleanForSlug.a98315ee.js";import"./constants.d64d7051.js";import"./html.5f1b4643.js";import"./Index.60834494.js";import"./Image.0bf5f897.js";import"./MaxCounts.12b45bab.js";import"./SaveChanges.176fcae6.js";import"./Img.5e5a9f8c.js";import"./Phone.c6cfe923.js";import"./preload-helper.b149fa8b.js";import"./RadioToggle.3d6c7d41.js";import"./SocialProfiles.ea8867dd.js";import"./Checkbox.a2fb80a8.js";import"./Checkmark.18246889.js";import"./Textarea.1b60c17b.js";import"./SettingsRow.7f1477b7.js";import"./Twitter.445d155c.js";import"./Plus.bd473ecb.js";import"./Header.45ff1370.js";import"./Logo.51893f13.js";import"./Steps.4b12c819.js";import"./HighlightToggle.57f326bf.js";import"./Radio.1c3a608c.js";import"./HtmlTagsEditor.5fcdcecc.js";import"./Editor.0df59dfe.js";import"./UnfilteredHtml.996ede2f.js";import"./ImageSeo.37c425ab.js";import"./ProBadge.bec762d2.js";import"./popup.b60b699f.js";import"./params.597cd0f5.js";import"./GoogleSearchPreview.4fcde61c.js";import"./PostTypeOptions.2b294ef3.js";import"./Tooltip.daabe115.js";import"./Book.5a25a725.js";import"./VideoCamera.c0a3e29c.js";const w={components:{AdditionalInformation:n,Category:p,Features:s,Import:a,LicenseKey:c,SearchAppearance:l,SmartRecommendations:u,Success:d,Welcome:f},computed:{...e("wizard",["shouldShowImportStep"]),...e(["isUnlicensed"]),...m("wizard",["stages"]),...m(["internalOptions"])},methods:{..._("wizard",["setStages","loadState"]),deleteStage(t){const o=[...this.stages],r=o.findIndex(i=>t===i);r!==-1&&o.splice(r,1),this.setStages(o)}},mounted(){if(this.internalOptions.internal.wizard){const t=JSON.parse(this.internalOptions.internal.wizard);delete t.currentStage,delete t.stages,delete t.licenseKey,this.loadState(t)}this.shouldShowImportStep||this.deleteStage("import"),this.isUnlicensed||this.deleteStage("license-key"),this.$isPro&&this.deleteStage("smart-recommendations")}};function x(t,o,r,i,z,$){return y(),S(g(t.$route.name))}const Lt=h(w,[["render",x]]);export{Lt as default};