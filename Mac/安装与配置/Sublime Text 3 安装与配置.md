### 1. 安装 Package Control

> 参考：[Package Control](https://packagecontrol.io/installation#st3)

使用快捷键`Ctrl + ~`调出 Sublime Text 的 console，在其中粘贴如下内容：

```Python
import urllib.request,os,hashlib; h = '6f4c264a24d933ce70df5dedcf1dcaee' + 'ebe013ee18cced0ef93d5f746d80ef60'; pf = 'Package Control.sublime-package'; ipp = sublime.installed_packages_path(); urllib.request.install_opener( urllib.request.build_opener( urllib.request.ProxyHandler()) ); by = urllib.request.urlopen( 'http://packagecontrol.io/' + pf.replace(' ', '%20')).read(); dh = hashlib.sha256(by).hexdigest(); print('Error validating download (got %s instead of %s), please try manual install' % (dh, h)) if dh != h else open(os.path.join( ipp, pf), 'wb' ).write(by)
```

执行完成之后，重启 Sublime Text，在`Perferences -> package settings`中可以看到`package control`，则表明安装成功。

### 2. 安装其他插件

安装好 Package Control 之后，可以通过快捷键`Shift + Command + P`调出包管理窗口，输入`pci`即可找到`Package Control: Install Package`，然后输入对应的包名称，找到包，按回车即可安装。

<img src="http://cnd.qiniu.lin07ux.cn/markdown/1521794771267.png" width="488"/>

主要安装的插件如下：

* A File Icon
* AdvancedNewFile
* Alignment
* AutoFileName
* Babel
* Blade Snippets
* Boxy Theme
* Codecs33
* ConvertToUTF8
* CSS Format
* DocBlockr
* EditorConfig
* EditorConfigSnippets
* Emmet
* FileDiffs
* GitGutter
* jQuery
* JsFormat
* Laravel 5 Snippets
* Laravel Balde Highlighter
* LESS
* Markdown Extended
* Minify
* PHP Companion
* Phpcs
* phpfmt
* PHPUnit Completions
* Side-by-Side Settings
* SideBarEnhancements
* View In Browser
* Vue Syntax Highlight

插件说明：

**View In Browser**

用于添加在浏览器中打开 HTML 文件的快捷键。安装完成后，使用默认的快捷键：

* Firefox 浏览器：`Ctrl + Alt + f`
* Chrome 浏览器：`Ctrl + Alt + c`
* IE 浏览器：`Ctrl + Alt + i`
* Safari 浏览器：`Ctrl + Alt + s`

如果想要自定义快捷键的话，可以在 Keybindings 中配置各浏览器快捷键。

```shell
{ "keys": [ "ctrl+alt+v" ], "command": "view_in_browser" }, { "keys": [ "f5" ], "command": "view_in_browser", "args": { "browser": "firefox" } }, { "keys": [ "f3" ], "command": "view_in_browser", "args": { "browser": "chrome" } }, { "keys": [ "ctrl+alt+i" ], "command": "view_in_browser", "args": { "browser": "iexplore" } }, { "keys": [ "f4" ], "command": "view_in_browser", "args": { "browser": "safari" } }
```

### 3. 设置主题

前面安装的主题是`Boxy Theme`，就可以通过`Perferences -> Theme...`和`Perferences -> Color Scheme`来设置主题和配色方案，这里选择使用的是`Boxy Monokai`主题，配色使用的是`Boxy Monokai`。

还有其他的设置，如：在文件末尾添加一个空行、字体、行高等。最终的用户配置文件如下：

```json
{
	"color_scheme": "Packages/Boxy Theme/schemes/Boxy Monokai.tmTheme",
	"ensure_newline_at_eof_on_save": true,
	"font_face": "Courier",
	"font_size": 16,
	"highlight_line": true,
	"ignored_packages":
	[
		"Vintage"
	],
	"line_padding_bottom": 5,
	"line_padding_top": 6,
	"shift_tab_unindent": true,
	"tab_size": 4,
	"theme": "Boxy Monokai.sublime-theme",
	"theme_accent_tangerine": true,
	"theme_autocomplete_item_selected_colored": true,
	"theme_bar_margin_top_sm": true,
	"theme_find_panel_close_hidden": true,
	"theme_find_panel_font_lg": true,
	"theme_find_panel_size_lg": true,
	"theme_icons_materialized": true,
	"theme_panel_switcher_atomized": true,
	"theme_quick_panel_item_selected_colored": true,
	"theme_quick_panel_size_md": true,
	"theme_scrollbar_colored": true,
	"theme_scrollbar_line": true,
	"theme_sidebar_folder_atomized": true,
	"theme_sidebar_font_lg": true,
	"theme_sidebar_size_md": true,
	"theme_statusbar_size_md": true,
	"theme_tab_close_always_visible": true,
	"theme_tab_font_lg": true,
	"theme_tab_selected_underlined": true,
	"theme_tab_separator": true,
	"theme_tab_size_lg": true,
	"theme_unified": true,
	"translate_tabs_to_spaces": true
}
```



