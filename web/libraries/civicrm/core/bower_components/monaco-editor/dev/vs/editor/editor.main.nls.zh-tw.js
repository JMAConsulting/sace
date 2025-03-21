/*!-----------------------------------------------------------
 * Copyright (c) Microsoft Corporation. All rights reserved.
 * Version: 0.16.1(3b771857b1c577594758bc2191049001394a1fda)
 * Released under the MIT license
 * https://github.com/Microsoft/vscode/blob/master/LICENSE.txt
 *-----------------------------------------------------------*/

define("vs/editor/editor.main.nls.zh-tw", {
	"vs/base/browser/ui/actionbar/actionbar": [
		"{0} ({1})",
	],
	"vs/base/browser/ui/aria/aria": [
		"{0} (occurred again)",
		"{0} (occurred {1} times)",
	],
	"vs/base/browser/ui/findinput/findInput": [
		"input",
	],
	"vs/base/browser/ui/findinput/findInputCheckboxes": [
		"Match Case",
		"Match Whole Word",
		"Use Regular Expression",
	],
	"vs/base/browser/ui/inputbox/inputBox": [
		"Error: {0}",
		"Warning: {0}",
		"Info: {0}",
	],
	"vs/base/browser/ui/keybindingLabel/keybindingLabel": [
		"Unbound",
	],
	"vs/base/browser/ui/list/listWidget": [
		"{0}. Use the navigation keys to navigate.",
	],
	"vs/base/browser/ui/menu/menu": [
		"{0} ({1})",
	],
	"vs/base/browser/ui/tree/abstractTree": [
		"Clear",
		"Disable Filter on Type",
		"Enable Filter on Type",
		"No elements found",
		"Matched {0} out of {1} elements",
	],
	"vs/base/common/keybindingLabels": [
		"Ctrl",
		"Shift",
		"Alt",
		"Windows",
		"Ctrl",
		"Shift",
		"Alt",
		"Super",
		"Control",
		"Shift",
		"Alt",
		"Command",
		"Control",
		"Shift",
		"Alt",
		"Windows",
		"Control",
		"Shift",
		"Alt",
		"Super",
	],
	"vs/base/common/severity": [
		"Error",
		"Warning",
		"Info",
	],
	"vs/base/parts/quickopen/browser/quickOpenModel": [
		"{0}, picker",
		"picker",
	],
	"vs/base/parts/quickopen/browser/quickOpenWidget": [
		"Quick picker. Type to narrow down results.",
		"Quick Picker",
		"{0} Results",
	],
	"vs/editor/browser/controller/coreCommands": [
		"&&Select All",
		"&&Undo",
		"&&Redo",
	],
	"vs/editor/browser/widget/codeEditorWidget": [
		"The number of cursors has been limited to {0}.",
	],
	"vs/editor/browser/widget/diffEditorWidget": [
		"Cannot compare files because one file is too large.",
	],
	"vs/editor/browser/widget/diffReview": [
		"Close",
		"no lines",
		"1 line",
		"{0} lines",
		"Difference {0} of {1}: original {2}, {3}, modified {4}, {5}",
		"blank",
		"original {0}, modified {1}: {2}",
		"+ modified {0}: {1}",
		"- original {0}: {1}",
		"Go to Next Difference",
		"Go to Previous Difference",
	],
	"vs/editor/common/config/commonEditorConfig": [
		"Editor",
		"Controls the font family.",
		"Controls the font weight.",
		"Controls the font size in pixels.",
		"Controls the line height. Use 0 to compute the line height from the font size.",
		"Controls the letter spacing in pixels.",
		"Line numbers are not rendered.",
		"Line numbers are rendered as absolute number.",
		"Line numbers are rendered as distance in lines to cursor position.",
		"Line numbers are rendered every 10 lines.",
		"Controls the display of line numbers.",
		"Render last line number when the file ends with a newline.",
		"Render vertical rulers after a certain number of monospace characters. Use multiple values for multiple rulers. No rulers are drawn if array is empty.",
		"Characters that will be used as word separators when doing word related navigations or operations.",
		"The number of spaces a tab is equal to. This setting is overridden based on the file contents when `#editor.detectIndentation#` is on.",
		"Insert spaces when pressing `Tab`. This setting is overridden based on the file contents when `#editor.detectIndentation#` is on.",
		"Controls whether `#editor.tabSize#` and `#editor.insertSpaces#` will be automatically detected when a file is opened based on the file contents.",
		"Controls whether selections should have rounded corners.",
		"Controls whether the editor will scroll beyond the last line.",
		"Controls the number of extra characters beyond which the editor will scroll horizontally.",
		"Controls whether the editor will scroll using an animation.",
		"Controls whether the minimap is shown.",
		"Controls the side where to render the minimap.",
		"Controls whether the minimap slider is automatically hidden.",
		"Render the actual characters on a line as opposed to color blocks.",
		"Limit the width of the minimap to render at most a certain number of columns.",
		"Controls whether the hover is shown.",
		"Controls the delay in milliseconds after which the hover is shown.",
		"Controls whether the hover should remain visible when mouse is moved over it.",
		"Controls whether the search string in the Find Widget is seeded from the editor selection.",
		"Controls whether the find operation is carried out on selected text or the entire file in the editor.",
		"Controls whether the Find Widget should read or modify the shared find clipboard on macOS.",
		"Controls whether the Find Widget should add extra lines on top of the editor. When true, you can scroll beyond the first line when the Find Widget is visible.",
		"Lines will never wrap.",
		"Lines will wrap at the viewport width.",
		"Lines will wrap at `#editor.wordWrapColumn#`.",
		"Lines will wrap at the minimum of viewport and `#editor.wordWrapColumn#`.",
		"Controls how lines should wrap.",
		"Controls the wrapping column of the editor when `#editor.wordWrap#` is `wordWrapColumn` or `bounded`.",
		"No indentation. Wrapped lines begin at column 1.",
		"Wrapped lines get the same indentation as the parent.",
		"Wrapped lines get +1 indentation toward the parent.",
		"Wrapped lines get +2 indentation toward the parent.",
		"Controls the indentation of wrapped lines.",
		"A multiplier to be used on the `deltaX` and `deltaY` of mouse wheel scroll events.",
		"Scrolling speed mulitiplier when pressing `Alt`.",
		"Maps to `Control` on Windows and Linux and to `Command` on macOS.",
		"Maps to `Alt` on Windows and Linux and to `Option` on macOS.",
		"The modifier to be used to add multiple cursors with the mouse. The Go To Definition and Open Link mouse gestures will adapt such that they do not conflict with the multicursor modifier. [Read more](https://code.visualstudio.com/docs/editor/codebasics#_multicursor-modifier).",
		"Merge multiple cursors when they are overlapping.",
		"Enable quick suggestions inside strings.",
		"Enable quick suggestions inside comments.",
		"Enable quick suggestions outside of strings and comments.",
		"Controls whether suggestions should automatically show up while typing.",
		"Controls the delay in milliseconds after which quick suggestions will show up.",
		"Enables a pop-up that shows parameter documentation and type information as you type.",
		"Controls whether the parameter hints menu cycles or closes when reaching the end of the list.",
		"Use language configurations to determine when to autoclose brackets.",
		"Autoclose brackets only when the cursor is to the left of whitespace.",
		"Controls whether the editor should automatically close brackets after the user adds an opening bracket.",
		"Use language configurations to determine when to autoclose quotes.",
		"Autoclose quotes only when the cursor is to the left of whitespace.",
		"Controls whether the editor should automatically close quotes after the user adds an opening quote.",
		"Use language configurations to determine when to automatically surround selections.",
		"Surround with brackets but not quotes.",
		"Surround with quotes but not brackets.",
		"Controls whether the editor should automatically surround selections.",
		"Controls whether the editor should automatically format the line after typing.",
		"Controls whether the editor should automatically format the pasted content. A formatter must be available and the formatter should be able to format a range in a document.",
		"Controls whether the editor should automatically adjust the indentation when users type, paste or move lines. Extensions with indentation rules of the language must be available.",
		"Controls whether suggestions should automatically show up when typing trigger characters.",
		"Only accept a suggestion with `Enter` when it makes a textual change.",
		"Controls whether suggestions should be accepted on `Enter`, in addition to `Tab`. Helps to avoid ambiguity between inserting new lines or accepting suggestions.",
		"Controls whether suggestions should be accepted on commit characters. For example, in JavaScript, the semi-colon (`;`) can be a commit character that accepts a suggestion and types that character.",
		"Show snippet suggestions on top of other suggestions.",
		"Show snippet suggestions below other suggestions.",
		"Show snippets suggestions with other suggestions.",
		"Do not show snippet suggestions.",
		"Controls whether snippets are shown with other suggestions and how they are sorted.",
		"Controls whether copying without a selection copies the current line.",
		"Controls whether syntax highlighting should be copied into the clipboard.",
		"Controls whether completions should be computed based on words in the document.",
		"Always select the first suggestion.",
		"Select recent suggestions unless further typing selects one, e.g. `console.| -> console.log` because `log` has been completed recently.",
		"Select suggestions based on previous prefixes that have completed those suggestions, e.g. `co -> console` and `con -> const`.",
		"Controls how suggestions are pre-selected when showing the suggest list.",
		"Font size for the suggest widget. When set to `0`, the value of `#editor.fontSize#` is used.",
		"Line height for the suggest widget. When set to `0`, the value of `#editor.lineHeight#` is used.",
		"Tab complete will insert the best matching suggestion when pressing tab.",
		"Disable tab completions.",
		"Tab complete snippets when their prefix match. Works best when \'quickSuggestions\' aren\'t enabled.",
		"Enables tab completions.",
		"Controls whether filtering and sorting suggestions accounts for small typos.",
		"Controls whether sorting favours words that appear close to the cursor.",
		"Controls whether remembered suggestion selections are shared between multiple workspaces and windows (needs `#editor.suggestSelection#`).",
		"Control whether an active snippet prevents quick suggestions.",
		"Controls whether the editor should highlight matches similar to the selection.",
		"Controls whether the editor should highlight semantic symbol occurrences.",
		"Controls the number of decorations that can show up at the same position in the overview ruler.",
		"Controls whether a border should be drawn around the overview ruler.",
		"Control the cursor animation style.",
		"Zoom the font of the editor when using mouse wheel and holding `Ctrl`.",
		"Controls whether the smooth caret animation should be enabled.",
		"Controls the cursor style.",
		"Controls the width of the cursor when `#editor.cursorStyle#` is set to `line`.",
		"Enables/Disables font ligatures.",
		"Controls whether the cursor should be hidden in the overview ruler.",
		"Render whitespace characters except for single spaces between words.",
		"Controls how the editor should render whitespace characters.",
		"Controls whether the editor should render control characters.",
		"Controls whether the editor should render indent guides.",
		"Controls whether the editor should highlight the active indent guide.",
		"Highlights both the gutter and the current line.",
		"Controls how the editor should render the current line highlight.",
		"Controls whether the editor shows CodeLens.",
		"Controls whether the editor has code folding enabled.",
		"Controls the strategy for computing folding ranges. `auto` uses a language specific folding strategy, if available. `indentation` uses the indentation based folding strategy.",
		"Controls whether the fold controls on the gutter are automatically hidden.",
		"Highlight matching brackets when one of them is selected.",
		"Controls whether the editor should render the vertical glyph margin. Glyph margin is mostly used for debugging.",
		"Inserting and deleting whitespace follows tab stops.",
		"Remove trailing auto inserted whitespace.",
		"Keep peek editors open even when double clicking their content or when hitting `Escape`.",
		"Controls whether the editor should allow moving selections via drag and drop.",
		"The editor will use platform APIs to detect when a Screen Reader is attached.",
		"The editor will be permanently optimized for usage with a Screen Reader.",
		"The editor will never be optimized for usage with a Screen Reader.",
		"Controls whether the editor should run in a mode where it is optimized for screen readers.",
		"Controls fading out of unused code.",
		"Controls whether the editor should detect links and make them clickable.",
		"Controls whether the editor should render the inline color decorators and color picker.",
		"Enables the code action lightbulb in the editor.",
		"Lines above this length will not be tokenized for performance reasons",
		"Controls whether organize imports action should be run on file save.",
		"Controls whether auto fix action should be run on file save.",
		"Code action kinds to be run on save.",
		"Timeout in milliseconds after which the code actions that are run on save are cancelled.",
		"Controls whether the Linux primary clipboard should be supported.",
		"Controls whether the diff editor shows the diff side by side or inline.",
		"Controls whether the diff editor shows changes in leading or trailing whitespace as diffs.",
		"Special handling for large files to disable certain memory intensive features.",
		"Controls whether the diff editor shows +/- indicators for added/removed changes.",
	],
	"vs/editor/common/config/editorOptions": [
		"The editor is not accessible at this time. Press Alt+F1 for options.",
		"Editor content",
	],
	"vs/editor/common/controller/cursor": [
		"Unexpected exception while executing command.",
	],
	"vs/editor/common/modes/modesRegistry": [
		"Plain Text",
	],
	"vs/editor/common/view/editorColorRegistry": [
		"Background color for the highlight of line at the cursor position.",
		"Background color for the border around the line at the cursor position.",
		"Background color of highlighted ranges, like by quick open and find features. The color must not be opaque so as not to hide underlying decorations.",
		"Background color of the border around highlighted ranges.",
		"Color of the editor cursor.",
		"The background color of the editor cursor. Allows customizing the color of a character overlapped by a block cursor.",
		"Color of whitespace characters in the editor.",
		"Color of the editor indentation guides.",
		"Color of the active editor indentation guides.",
		"Color of editor line numbers.",
		"Color of editor active line number",
		"Id is deprecated. Use \'editorLineNumber.activeForeground\' instead.",
		"Color of editor active line number",
		"Color of the editor rulers.",
		"Foreground color of editor code lenses",
		"Background color behind matching brackets",
		"Color for matching brackets boxes",
		"Color of the overview ruler border.",
		"Background color of the editor gutter. The gutter contains the glyph margins and the line numbers.",
		"Foreground color of error squigglies in the editor.",
		"Border color of error squigglies in the editor.",
		"Foreground color of warning squigglies in the editor.",
		"Border color of warning squigglies in the editor.",
		"Foreground color of info squigglies in the editor.",
		"Border color of info squigglies in the editor.",
		"Foreground color of hint squigglies in the editor.",
		"Border color of hint squigglies in the editor.",
		"Border color of unnecessary (unused) source code in the editor.",
		"Opacity of unnecessary (unused) source code in the editor. For example, \"#000000c0\" will render the code with 75% opacity. For high contrast themes, use the  \'editorUnnecessaryCode.border\' theme color to underline unnecessary code instead of fading it out.",
		"Overview ruler marker color for errors.",
		"Overview ruler marker color for warnings.",
		"Overview ruler marker color for infos.",
	],
	"vs/editor/contrib/bracketMatching/bracketMatching": [
		"Overview ruler marker color for matching brackets.",
		"Go to Bracket",
		"Select to Bracket",
		"Go to &&Bracket",
	],
	"vs/editor/contrib/caretOperations/caretOperations": [
		"Move Caret Left",
		"Move Caret Right",
	],
	"vs/editor/contrib/caretOperations/transpose": [
		"Transpose Letters",
	],
	"vs/editor/contrib/clipboard/clipboard": [
		"Cut",
		"Cu&&t",
		"Copy",
		"&&Copy",
		"Paste",
		"&&Paste",
		"Copy With Syntax Highlighting",
	],
	"vs/editor/contrib/codeAction/codeActionCommands": [
		"Show Fixes ({0})",
		"Show Fixes",
		"Quick Fix...",
		"No code actions available",
		"No code actions available",
		"Refactor...",
		"No refactorings available",
		"Source Action...",
		"No source actions available",
		"Organize Imports",
		"No organize imports action available",
		"Auto Fix...",
		"No auto fixes available",
	],
	"vs/editor/contrib/comment/comment": [
		"Toggle Line Comment",
		"&&Toggle Line Comment",
		"Add Line Comment",
		"Remove Line Comment",
		"Toggle Block Comment",
		"Toggle &&Block Comment",
	],
	"vs/editor/contrib/contextmenu/contextmenu": [
		"Show Editor Context Menu",
	],
	"vs/editor/contrib/cursorUndo/cursorUndo": [
		"Soft Undo",
	],
	"vs/editor/contrib/find/findController": [
		"Find",
		"&&Find",
		"Find With Selection",
		"Find Next",
		"Find Previous",
		"Find Next Selection",
		"Find Previous Selection",
		"Replace",
		"&&Replace",
	],
	"vs/editor/contrib/find/findWidget": [
		"Find",
		"Find",
		"Previous match",
		"Next match",
		"Find in selection",
		"Close",
		"Replace",
		"Replace",
		"Replace",
		"Replace All",
		"Toggle Replace mode",
		"Only the first {0} results are highlighted, but all find operations work on the entire text.",
		"{0} of {1}",
		"No Results",
	],
	"vs/editor/contrib/folding/folding": [
		"Unfold",
		"Unfold Recursively",
		"Fold",
		"Fold Recursively",
		"Fold All Block Comments",
		"Fold All Regions",
		"Unfold All Regions",
		"Fold All",
		"Unfold All",
		"Fold Level {0}",
	],
	"vs/editor/contrib/fontZoom/fontZoom": [
		"Editor Font Zoom In",
		"Editor Font Zoom Out",
		"Editor Font Zoom Reset",
	],
	"vs/editor/contrib/format/formatActions": [
		"Made 1 formatting edit on line {0}",
		"Made {0} formatting edits on line {1}",
		"Made 1 formatting edit between lines {0} and {1}",
		"Made {0} formatting edits between lines {1} and {2}",
		"Format Document",
		"Format Selection",
	],
	"vs/editor/contrib/goToDefinition/goToDefinitionCommands": [
		"No definition found for \'{0}\'",
		"No definition found",
		" – {0} definitions",
		"Go to Definition",
		"Open Definition to the Side",
		"Peek Definition",
		"No declaration found for \'{0}\'",
		"No declaration found",
		" – {0} declarations",
		"Go to Declaration",
		"No declaration found for \'{0}\'",
		"No declaration found",
		" – {0} declarations",
		"Peek Declaration",
		"No implementation found for \'{0}\'",
		"No implementation found",
		" – {0} implementations",
		"Go to Implementation",
		"Peek Implementation",
		"No type definition found for \'{0}\'",
		"No type definition found",
		" – {0} type definitions",
		"Go to Type Definition",
		"Peek Type Definition",
		"Go to &&Definition",
		"Go to &&Type Definition",
		"Go to &&Implementation",
	],
	"vs/editor/contrib/goToDefinition/goToDefinitionMouse": [
		"Click to show {0} definitions.",
	],
	"vs/editor/contrib/gotoError/gotoError": [
		"Go to Next Problem (Error, Warning, Info)",
		"Go to Previous Problem (Error, Warning, Info)",
		"Go to Next Problem in Files (Error, Warning, Info)",
		"Go to Previous Problem in Files (Error, Warning, Info)",
		"Next &&Problem",
		"Previous &&Problem",
	],
	"vs/editor/contrib/gotoError/gotoErrorWidget": [
		"{0} of {1} problems",
		"{0} of {1} problem",
		"Editor marker navigation widget error color.",
		"Editor marker navigation widget warning color.",
		"Editor marker navigation widget info color.",
		"Editor marker navigation widget background.",
	],
	"vs/editor/contrib/hover/hover": [
		"Show Hover",
	],
	"vs/editor/contrib/hover/modesContentHover": [
		"Loading...",
		"Quick Fix...",
		"Peek Problem",
		"No code actions available",
	],
	"vs/editor/contrib/inPlaceReplace/inPlaceReplace": [
		"Replace with Previous Value",
		"Replace with Next Value",
	],
	"vs/editor/contrib/linesOperations/linesOperations": [
		"Copy Line Up",
		"&&Copy Line Up",
		"Copy Line Down",
		"Co&&py Line Down",
		"Move Line Up",
		"Mo&&ve Line Up",
		"Move Line Down",
		"Move &&Line Down",
		"Sort Lines Ascending",
		"Sort Lines Descending",
		"Trim Trailing Whitespace",
		"Delete Line",
		"Indent Line",
		"Outdent Line",
		"Insert Line Above",
		"Insert Line Below",
		"Delete All Left",
		"Delete All Right",
		"Join Lines",
		"Transpose characters around the cursor",
		"Transform to Uppercase",
		"Transform to Lowercase",
	],
	"vs/editor/contrib/links/links": [
		"Cmd + click to follow link",
		"Ctrl + click to follow link",
		"Cmd + click to execute command",
		"Ctrl + click to execute command",
		"Option + click to follow link",
		"Alt + click to follow link",
		"Option + click to execute command",
		"Alt + click to execute command",
		"Failed to open this link because it is not well-formed: {0}",
		"Failed to open this link because its target is missing.",
		"Open Link",
	],
	"vs/editor/contrib/message/messageController": [
		"Cannot edit in read-only editor",
	],
	"vs/editor/contrib/multicursor/multicursor": [
		"Add Cursor Above",
		"&&Add Cursor Above",
		"Add Cursor Below",
		"A&&dd Cursor Below",
		"Add Cursors to Line Ends",
		"Add C&&ursors to Line Ends",
		"Add Cursors To Bottom",
		"Add Cursors To Top",
		"Add Selection To Next Find Match",
		"Add &&Next Occurrence",
		"Add Selection To Previous Find Match",
		"Add P&&revious Occurrence",
		"Move Last Selection To Next Find Match",
		"Move Last Selection To Previous Find Match",
		"Select All Occurrences of Find Match",
		"Select All &&Occurrences",
		"Change All Occurrences",
	],
	"vs/editor/contrib/parameterHints/parameterHints": [
		"Trigger Parameter Hints",
	],
	"vs/editor/contrib/parameterHints/parameterHintsWidget": [
		"{0}, hint",
	],
	"vs/editor/contrib/referenceSearch/peekViewWidget": [
		"Close",
	],
	"vs/editor/contrib/referenceSearch/referenceSearch": [
		" – {0} references",
		"Peek References",
	],
	"vs/editor/contrib/referenceSearch/referencesController": [
		"Loading...",
	],
	"vs/editor/contrib/referenceSearch/referencesModel": [
		"symbol in {0} on line {1} at column {2}",
		"1 symbol in {0}, full path {1}",
		"{0} symbols in {1}, full path {2}",
		"No results found",
		"Found 1 symbol in {0}",
		"Found {0} symbols in {1}",
		"Found {0} symbols in {1} files",
	],
	"vs/editor/contrib/referenceSearch/referencesTree": [
		"Failed to resolve file.",
		"{0} references",
		"{0} reference",
	],
	"vs/editor/contrib/referenceSearch/referencesWidget": [
		"no preview available",
		"References",
		"No results",
		"References",
		"Background color of the peek view title area.",
		"Color of the peek view title.",
		"Color of the peek view title info.",
		"Color of the peek view borders and arrow.",
		"Background color of the peek view result list.",
		"Foreground color for line nodes in the peek view result list.",
		"Foreground color for file nodes in the peek view result list.",
		"Background color of the selected entry in the peek view result list.",
		"Foreground color of the selected entry in the peek view result list.",
		"Background color of the peek view editor.",
		"Background color of the gutter in the peek view editor.",
		"Match highlight color in the peek view result list.",
		"Match highlight color in the peek view editor.",
		"Match highlight border in the peek view editor.",
	],
	"vs/editor/contrib/rename/rename": [
		"No result.",
		"An unknown error occurred while resolving rename location",
		"Successfully renamed \'{0}\' to \'{1}\'. Summary: {2}",
		"Rename failed to execute.",
		"Rename Symbol",
	],
	"vs/editor/contrib/rename/renameInputField": [
		"Rename input. Type new name and press Enter to commit.",
	],
	"vs/editor/contrib/smartSelect/smartSelect": [
		"Expand Selection",
		"&&Expand Selection",
		"Shrink Selection",
		"&&Shrink Selection",
	],
	"vs/editor/contrib/snippet/snippetVariables": [
		"Sunday",
		"Monday",
		"Tuesday",
		"Wednesday",
		"Thursday",
		"Friday",
		"Saturday",
		"Sun",
		"Mon",
		"Tue",
		"Wed",
		"Thu",
		"Fri",
		"Sat",
		"January",
		"February",
		"March",
		"April",
		"May",
		"June",
		"July",
		"August",
		"September",
		"October",
		"November",
		"December",
		"Jan",
		"Feb",
		"Mar",
		"Apr",
		"May",
		"Jun",
		"Jul",
		"Aug",
		"Sep",
		"Oct",
		"Nov",
		"Dec",
	],
	"vs/editor/contrib/suggest/suggestController": [
		"Accepting \'{0}\' did insert the following text: {1}",
		"Trigger Suggest",
	],
	"vs/editor/contrib/suggest/suggestWidget": [
		"Background color of the suggest widget.",
		"Border color of the suggest widget.",
		"Foreground color of the suggest widget.",
		"Background color of the selected entry in the suggest widget.",
		"Color of the match highlights in the suggest widget.",
		"Read More...{0}",
		"Read less...{0}",
		"Loading...",
		"No suggestions.",
		"{0}, accepted",
		"{0}, snippet suggestion",
		"{0}, suggestion",
		"{0}, snippet suggestion. Reading details. {1}",
		"{0}, suggestion. Reading details. {1}",
		"{0}, snippet suggestion, has details",
		"{0}, suggestion, has details",
	],
	"vs/editor/contrib/toggleTabFocusMode/toggleTabFocusMode": [
		"Toggle Tab Key Moves Focus",
		"Pressing Tab will now move focus to the next focusable element",
		"Pressing Tab will now insert the tab character",
	],
	"vs/editor/contrib/tokenization/tokenization": [
		"Developer: Force Retokenize",
	],
	"vs/editor/contrib/wordHighlighter/wordHighlighter": [
		"Background color of a symbol during read-access, like reading a variable. The color must not be opaque so as not to hide underlying decorations.",
		"Background color of a symbol during write-access, like writing to a variable. The color must not be opaque so as not to hide underlying decorations.",
		"Border color of a symbol during read-access, like reading a variable.",
		"Border color of a symbol during write-access, like writing to a variable.",
		"Overview ruler marker color for symbol highlights. The color must not be opaque so as not to hide underlying decorations.",
		"Overview ruler marker color for write-access symbol highlights. The color must not be opaque so as not to hide underlying decorations.",
		"Go to Next Symbol Highlight",
		"Go to Previous Symbol Highlight",
		"Trigger Symbol Highlight",
	],
	"vs/editor/standalone/browser/accessibilityHelp/accessibilityHelp": [
		"No selection",
		"Line {0}, Column {1} ({2} selected)",
		"Line {0}, Column {1}",
		"{0} selections ({1} characters selected)",
		"{0} selections",
		"Now changing the setting `accessibilitySupport` to \'on\'.",
		"Now opening the Editor Accessibility documentation page.",
		" in a read-only pane of a diff editor.",
		" in a pane of a diff editor.",
		" in a read-only code editor",
		" in a code editor",
		"To configure the editor to be optimized for usage with a Screen Reader press Command+E now.",
		"To configure the editor to be optimized for usage with a Screen Reader press Control+E now.",
		"The editor is configured to be optimized for usage with a Screen Reader.",
		"The editor is configured to never be optimized for usage with a Screen Reader, which is not the case at this time.",
		"Pressing Tab in the current editor will move focus to the next focusable element. Toggle this behavior by pressing {0}.",
		"Pressing Tab in the current editor will move focus to the next focusable element. The command {0} is currently not triggerable by a keybinding.",
		"Pressing Tab in the current editor will insert the tab character. Toggle this behavior by pressing {0}.",
		"Pressing Tab in the current editor will insert the tab character. The command {0} is currently not triggerable by a keybinding.",
		"Press Command+H now to open a browser window with more information related to editor accessibility.",
		"Press Control+H now to open a browser window with more information related to editor accessibility.",
		"You can dismiss this tooltip and return to the editor by pressing Escape or Shift+Escape.",
		"Show Accessibility Help",
	],
	"vs/editor/standalone/browser/inspectTokens/inspectTokens": [
		"Developer: Inspect Tokens",
	],
	"vs/editor/standalone/browser/quickOpen/gotoLine": [
		"Go to line {0} and character {1}",
		"Go to line {0}",
		"Type a line number between 1 and {0} to navigate to",
		"Type a character between 1 and {0} to navigate to",
		"Current Line: {0}. Go to line {0}.",
		"Type a line number, followed by an optional colon and a character number to navigate to",
		"Go to Line...",
	],
	"vs/editor/standalone/browser/quickOpen/quickCommand": [
		"{0}, {1}, commands",
		"{0}, commands",
		"Type the name of an action you want to execute",
		"Command Palette",
	],
	"vs/editor/standalone/browser/quickOpen/quickOutline": [
		"{0}, symbols",
		"Type the name of an identifier you wish to navigate to",
		"Go to Symbol...",
		"symbols ({0})",
		"modules ({0})",
		"classes ({0})",
		"interfaces ({0})",
		"methods ({0})",
		"functions ({0})",
		"properties ({0})",
		"variables ({0})",
		"variables ({0})",
		"constructors ({0})",
		"calls ({0})",
	],
	"vs/editor/standalone/browser/simpleServices": [
		"Made {0} edits in {1} files",
	],
	"vs/editor/standalone/browser/standaloneCodeEditor": [
		"Editor content",
		"Press Ctrl+F1 for Accessibility Options.",
		"Press Alt+F1 for Accessibility Options.",
	],
	"vs/editor/standalone/browser/toggleHighContrast/toggleHighContrast": [
		"Toggle High Contrast Theme",
	],
	"vs/platform/configuration/common/configurationRegistry": [
		"Default Configuration Overrides",
		"Configure editor settings to be overridden for a language.",
		"Cannot register \'{0}\'. This matches property pattern \'\\\\[.*\\\\]$\' for describing language specific editor settings. Use \'configurationDefaults\' contribution.",
		"Cannot register \'{0}\'. This property is already registered.",
	],
	"vs/platform/keybinding/common/abstractKeybindingService": [
		"({0}) was pressed. Waiting for second key of chord...",
		"The key combination ({0}, {1}) is not a command.",
	],
	"vs/platform/list/browser/listService": [
		"Workbench",
		"Maps to `Control` on Windows and Linux and to `Command` on macOS.",
		"Maps to `Alt` on Windows and Linux and to `Option` on macOS.",
		"The modifier to be used to add an item in trees and lists to a multi-selection with the mouse (for example in the explorer, open editors and scm view). The \'Open to Side\' mouse gestures - if supported - will adapt such that they do not conflict with the multiselect modifier.",
		"Controls how to open items in trees and lists using the mouse (if supported). For parents with children in trees, this setting will control if a single click expands the parent or a double click. Note that some trees and lists might choose to ignore this setting if it is not applicable. ",
		"Controls whether lists and trees support horizontal scrolling in the workbench.",
		"Controls whether trees support horizontal scrolling in the workbench.",
		"This setting is deprecated, please use \'{0}\' instead.",
		"Controls tree indentation in pixels.",
		"Simple keyboard navigation focuses elements which match the keyboard input. Matching is done only on prefixes.",
		"Highlight keyboard navigation highlights elements which match the keyboard input. Further up and down navigation will traverse only the highlighted elements.",
		"Filter keyboard navigation will filter out and hide all the elements which do not match the keyboard input.",
		"Controls the keyboard navigation style for lists and trees in the workbench. Can be simple, highlight and filter.",
		"Controls whether keyboard navigation in lists and trees is automatically triggered simply by typing. If set to `false`, keyboard navigation is only triggered when executing the `list.toggleKeyboardNavigation` command, for which you can assign a keyboard shortcut.",
	],
	"vs/platform/markers/common/markers": [
		"Error",
		"Warning",
		"Info",
	],
	"vs/platform/theme/common/colorRegistry": [
		"Overall foreground color. This color is only used if not overridden by a component.",
		"Overall foreground color for error messages. This color is only used if not overridden by a component.",
		"Overall border color for focused elements. This color is only used if not overridden by a component.",
		"An extra border around elements to separate them from others for greater contrast.",
		"An extra border around active elements to separate them from others for greater contrast.",
		"Foreground color for links in text.",
		"Background color for code blocks in text.",
		"Shadow color of widgets such as find/replace inside the editor.",
		"Input box background.",
		"Input box foreground.",
		"Input box border.",
		"Border color of activated options in input fields.",
		"Input validation background color for information severity.",
		"Input validation foreground color for information severity.",
		"Input validation border color for information severity.",
		"Input validation background color for warning severity.",
		"Input validation foreground color for warning severity.",
		"Input validation border color for warning severity.",
		"Input validation background color for error severity.",
		"Input validation foreground color for error severity.",
		"Input validation border color for error severity.",
		"Dropdown background.",
		"Dropdown foreground.",
		"List/Tree background color for the focused item when the list/tree is active. An active list/tree has keyboard focus, an inactive does not.",
		"List/Tree foreground color for the focused item when the list/tree is active. An active list/tree has keyboard focus, an inactive does not.",
		"List/Tree background color for the selected item when the list/tree is active. An active list/tree has keyboard focus, an inactive does not.",
		"List/Tree foreground color for the selected item when the list/tree is active. An active list/tree has keyboard focus, an inactive does not.",
		"List/Tree background color for the selected item when the list/tree is inactive. An active list/tree has keyboard focus, an inactive does not.",
		"List/Tree foreground color for the selected item when the list/tree is inactive. An active list/tree has keyboard focus, an inactive does not.",
		"List/Tree background color for the focused item when the list/tree is inactive. An active list/tree has keyboard focus, an inactive does not.",
		"List/Tree background when hovering over items using the mouse.",
		"List/Tree foreground when hovering over items using the mouse.",
		"List/Tree drag and drop background when moving items around using the mouse.",
		"List/Tree foreground color of the match highlights when searching inside the list/tree.",
		"Background color of the type filter widget in lists and trees.",
		"Outline color of the type filter widget in lists and trees.",
		"Outline color of the type filter widget in lists and trees, when there are no matches.",
		"Quick picker color for grouping labels.",
		"Quick picker color for grouping borders.",
		"Badge background color. Badges are small information labels, e.g. for search results count.",
		"Badge foreground color. Badges are small information labels, e.g. for search results count.",
		"Scrollbar shadow to indicate that the view is scrolled.",
		"Scrollbar slider background color.",
		"Scrollbar slider background color when hovering.",
		"Scrollbar slider background color when clicked on.",
		"Background color of the progress bar that can show for long running operations.",
		"Border color of menus.",
		"Foreground color of menu items.",
		"Background color of menu items.",
		"Foreground color of the selected menu item in menus.",
		"Background color of the selected menu item in menus.",
		"Border color of the selected menu item in menus.",
		"Color of a separator menu item in menus.",
		"Editor background color.",
		"Editor default foreground color.",
		"Background color of editor widgets, such as find/replace.",
		"Border color of editor widgets. The color is only used if the widget chooses to have a border and if the color is not overridden by a widget.",
		"Border color of the resize bar of editor widgets. The color is only used if the widget chooses to have a resize border and if the color is not overridden by a widget.",
		"Color of the editor selection.",
		"Color of the selected text for high contrast.",
		"Color of the selection in an inactive editor. The color must not be opaque so as not to hide underlying decorations.",
		"Color for regions with the same content as the selection. The color must not be opaque so as not to hide underlying decorations.",
		"Border color for regions with the same content as the selection.",
		"Color of the current search match.",
		"Color of the other search matches. The color must not be opaque so as not to hide underlying decorations.",
		"Color of the range limiting the search. The color must not be opaque so as not to hide underlying decorations.",
		"Border color of the current search match.",
		"Border color of the other search matches.",
		"Border color of the range limiting the search. The color must not be opaque so as not to hide underlying decorations.",
		"Highlight below the word for which a hover is shown. The color must not be opaque so as not to hide underlying decorations.",
		"Background color of the editor hover.",
		"Border color of the editor hover.",
		"Background color of the editor hover status bar.",
		"Color of active links.",
		"Background color for text that got inserted. The color must not be opaque so as not to hide underlying decorations.",
		"Background color for text that got removed. The color must not be opaque so as not to hide underlying decorations.",
		"Outline color for the text that got inserted.",
		"Outline color for text that got removed.",
		"Border color between the two text editors.",
		"Highlight background color of a snippet tabstop.",
		"Highlight border color of a snippet tabstop.",
		"Highlight background color of the final tabstop of a snippet.",
		"Highlight border color of the final stabstop of a snippet.",
		"Overview ruler marker color for find matches. The color must not be opaque so as not to hide underlying decorations.",
		"Overview ruler marker color for selection highlights. The color must not be opaque so as not to hide underlying decorations.",
	]
});