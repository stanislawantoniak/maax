/**
 * MagPleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE.txt
 *
 * @category   Magpleasure
 * @package    Magpleasure_Blog
 * @copyright  Copyright (c) 2012-2015 MagPleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */

(function() {
	tinymce.create('tinymce.plugins.BlogCutPlugin', {
		init : function(ed, url) {

			var pb = '<hr class="mceBlogCut mceItemNoResize" id="mceBlogCut" style="border: 0 none; border-top: 2px dashed #AAAAAA;" />';
            var cls = 'mceBlogCut';
            var sep = ed.getParam('blogcut_separator', '<!-- blogcut -->');
            var pbRE = pbRE = new RegExp(sep.replace(/[\?\.\*\[\]\(\)\{\}\+\^\$\:]/g, function(a) {return '\\' + a;}), 'g');

			// Register commands
			ed.addCommand('mceBlogCut', function() {

                if ($('display_short_content').value == '1'){
                    hideShortContent();
                }

                ed.selection.setContent('tiny_mce_marker');
                ed.setContent(ed.getContent().replace(pbRE, '').replace(/tiny_mce_marker/g, pb));
			});

			// Register buttons
			ed.addButton('blogcut', {title : 'More Tag', cmd : cls});

			ed.onInit.add(function() {
				if (ed.theme.onResolveName) {
					ed.theme.onResolveName.add(function(th, o) {
						if (o.node.nodeName == 'HR' && ed.dom.hasClass(o.node, cls)){
                            o.name = 'blogcut';
                        }
					});
				}
			});

			ed.onClick.add(function(ed, e) {
				e = e.target;
				if (e.nodeName === 'HR' && ed.dom.hasClass(e, cls)){
                    ed.selection.select(e);
                }
			});

			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('blogcut', n.nodeName === 'HR' && ed.dom.hasClass(n, cls));
			});

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = o.content.replace(pbRE, pb);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.get)
					o.content = o.content.replace(/<hr[^>]+>/g, function(im) {
						if (im.indexOf('class="mceBlogCut') !== -1){
							im = sep;
                        }
						return im;
					});
			});
		},

		getInfo : function() {
			return {
				longname : 'Blog More Tag',
				author : 'MagPleasure',
				authorurl : 'http://www.magpleasure.com',
				infourl : 'http://www.magpleasure.com',
				version : '1.1'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('blogcut', tinymce.plugins.BlogCutPlugin);
})();