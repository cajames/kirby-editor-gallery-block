# Kirby Editor - Gallery Block

> A gallery module for the Kirby CMS Editor

This module is mostly built on top of existing Kirby UI Kit components.

## Features

- Auto-layout for multiple images.
- Drag and drop images to position.
- Default render snippet which renders what's in the Editor
- Ability to add custom CSS classes to each block, row and image from the Editor.
- Ability to add Alt text to each image.

![gallery-block-demo](https://user-images.githubusercontent.com/1523286/84902687-7896a680-b0f0-11ea-871d-75a64442f0f7.gif)

## Installation

- Download this repository as a `.zip`
- Extract it into your `site/plugins` folder with the name `kirby-editor-gallery-block`

## Usage

I've attempted to follow the same UX patterns as other Kirby Editor blocks

- Images can be dragged onto the gallery (will be uploaded), or selected from existing files in the page.
- Pressing `Backspace` on an image will remove the image from the gallery.
- Pressing `Enter` on an image will add a new gallery row below.
- Pressing `Enter` on an empty row, will convert it to a paragraph.
- Each row is a block, and can be re-ordered.
- Each image can be re-ordered by dragging.
- Keyboard navigation works to navigate the blocks

The project comes with [a default snippet to render the gallery blocks](https://github.com/cajames/kirby-editor-gallery-block/tree/master/snippets) in your templates as displayed in the editor. This can be edited to suit your needs.

## Local Development

```sh
# From project root folder, install dependencies
$ yarn

# For development and live debug
$ yarn dev

# For production build
$ yarn build
```

## License

- MIT
- Still a work in progress, use at your own risk.
