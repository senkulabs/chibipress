import { Editor } from "@tiptap/core";
import Highlight from "@tiptap/extension-highlight";
import Underline from "@tiptap/extension-underline";
import Document from '@tiptap/extension-document';
import Bold from '@tiptap/extension-bold';
import Italic from '@tiptap/extension-italic';
import Paragraph from '@tiptap/extension-paragraph';
import Text from '@tiptap/extension-text';
import Heading from '@tiptap/extension-heading';
import BulletList from '@tiptap/extension-bullet-list';
import ListItem from '@tiptap/extension-list-item';
import OrderedList from '@tiptap/extension-ordered-list';

document.addEventListener("alpine:init", () => {
  window.richEditor = function(content) {
    let editor;
    let isActive = {
        bold: false,
        italic: false,
        underline: false,
        highlight: false,
        heading1: false,
        heading2: false,
        heading3: false,
        bulletList: false,
        orderedList: false,
    };

    return {
      content: content,
      isActive: isActive,
      updatedAt: Date.now(), // force Alpine to rerender on selection change
      init(element) {
        const _this = this;
        editor = new Editor({
          element: element,
          editorProps: {
            attributes: {
              class:
                "p-2 prose-sm min-h-60 prose-h1:mb-0 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 focus:outline-none"
            }
          },
          extensions: [
            Document,
            Paragraph,
            Text,
            Bold,
            Italic,
            Underline,
            Highlight,
            Heading.configure({
                levels: [1, 2, 3]
            }),
            BulletList,
            OrderedList,
            ListItem,
          ],
          content: this.content,
          onCreate({ editor }) {
            _this.updatedAt = Date.now();
          },
          onUpdate: ({ editor }) => {
            _this.updatedAt = Date.now();
            _this.content = editor.getHTML();
          },
          onSelectionUpdate({ editor }) {
            _this.updatedAt = Date.now();
            _this.updateActiveStates();
          }
        });

        this.editor = editor;
        this.updateActiveStates();

        editor.view.dom.addEventListener('keydown', (e) => {
            _this.updateActiveStates();
            _this.updatedAt = Date.now();
        })

        this.$watch("content", content => {
          // If the new content matches TipTap's then we just skip.
          if (content === editor.getHTML()) return;
          /*
            Otherwise, it means that a force external to TipTap
            is modifying the data on this Alpine component,
            which could be Livewire itself.
            In this case, we just need to update TipTap's
            content and we're good to do.
            For more information on the `setContent()` method, see:
              https://www.tiptap.dev/api/commands/set-content
          */
          editor.commands.setContent(content, false);
        });
      },
      updateActiveStates() {
        this.isActive = {
            bold: editor.isActive('bold'),
            italic: editor.isActive('italic'),
            underline: editor.isActive('underline'),
            highlight: editor.isActive('highlight'),
            heading1: editor.isActive('heading', { level: 1 }),
            heading2: editor.isActive('heading', { level: 2 }),
            heading3: editor.isActive('heading', { level: 3 }),
            bulletList: editor.isActive('bulletList'),
            orderedList: editor.isActive('orderedList'),
        };
      },
      toggleBold() {
        editor.chain().toggleBold().focus().run();
        this.updateActiveStates();
      },
      toggleItalic() {
        editor.chain().toggleItalic().focus().run();
        this.updateActiveStates();
      },
      toggleUnderline() {
        editor.chain().toggleUnderline().focus().run();
        this.updateActiveStates();
      },
      toggleHighlight() {
        editor.chain().toggleHighlight().focus().run();
        this.updateActiveStates();
      },
      toggleHeading(level) {
        editor.chain().toggleHeading(level).focus().run();
        this.updateActiveStates();
      },
      toggleBulletList() {
        editor.chain().toggleBulletList().focus().run();
        this.updateActiveStates();
      },
      toggleOrderedList() {
        editor.chain().toggleOrderedList().focus().run();
        this.updateActiveStates();
      },
    };
  };
});
