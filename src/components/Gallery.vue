<template>
  <div
    class="k-editor-gallery-block"
    tabindex="0"
    ref="element"
    @keydown.delete.prevent.self="$emit('remove')"
    @keydown.enter.self="$emit('convert', 'paragraph')"
    @keydown.up="$emit('prev')"
    @keydown.down="$emit('next')"
  >
    <k-dropzone @drop="(files) => onDrop(files)">
      <div class="k-editor-gallery-row">
        <!-- Empty Case -->
        <div v-if="images.length === 0" class="k-editor-gallery-row-empty">
          <button class="k-button" @keydown.enter.native.stop @click.stop.prevent="uploadFiles">
            <k-icon type="upload"></k-icon>
            <span class="k-button-text">Upload images</span>
          </button>
          <span class="separator">or</span>
          <button class="k-button" @keydown.enter.native.stop @click.stop.prevent="selectFiles">
            <k-icon type="folder"></k-icon>
            <span class="k-button-text">Select images</span>
          </button>
        </div>

        <k-draggable
          :options="{ group: attrs.group }"
          :list="images"
          class="k-editor-gallery-row-draggable"
          :class="{ 'empty-draggable': images.length === 0 }"
          @end="onDragEnd"
        >
          <div
            v-for="(image, imageIndex) in images"
            :key="image.guid"
            :style="calcImageDimentions(images, image)"
            class="k-editor-row-image k-editor-block-gallery-image"
            @keydown.delete.prevent.self="deleteImage(imageIndex)"
            @keydown.enter.self="addImageRow"
            @keydown.left.self="focusImage(imageIndex - 1)"
            @keydown.right.self="focusImage(imageIndex + 1)"
            tabindex="0"
          >
            <img @dragstart.prevent :data-src="image.src" alt />
          </div>
        </k-draggable>
      </div>
    </k-dropzone>

    <!-- Files Selector Dialog -->
    <k-files-dialog ref="fileDialog" @submit="insertFiles($event)" />
    <!-- Upload File Dialog -->
    <k-upload ref="fileUpload" @success="insertUpload" />
    <!-- Settings Dialog -->
    <k-dialog ref="settings" @submit="saveSettings" size="medium">
      <k-form :fields="fields" v-model="attrs" @submit="saveSettings" />
    </k-dialog>
  </div>
</template>

<script>
import lozad from "lozad";
const sortAlphaNum = (a, b) =>
  a.id.localeCompare(b.id, "en", { numeric: true });

export default {
  icon: "file-image",
  label: "Gallery",
  props: {
    attrs: {
      type: Object,
      default: () => ({})
    },
    endpoints: Object,
    spellcheck: Boolean
  },
  data: () => ({
    images: []
  }),
  created() {
    if (!this.attrs.images) {
      this.input({
        group: "default",
        images: []
      });
    } else {
      this.images = this.attrs.images;
    }
  },
  watch: {
    attrs: function(val) {
      this.images = val.images;
    }
  },
  computed: {
    style() {
      if (this.attrs.ratio) {
        return "padding-bottom:" + 100 / this.attrs.ratio + "%";
      }
    },
    fields() {
      return {
        group: {
          label: this.$t("editor.blocks.gallery.settings.group.label"),
          type: "text",
          icon: "layers"
        },
        imageAlt: {
          label: this.$t("editor.blocks.gallery.settings.alt.label"),
          type: "text",
          icon: "layers"
        }
      };
    }
  },
  mounted() {
    this.lazyLoadImages();
  },
  methods: {
    async focus(options = {}) {
      await this.$nextTick();
      const element = this.$refs.element;

      if (options.focusRoot === true || this.images.length === 0) {
        this.$refs.element.focus();
        return;
      }

      const images = Array.from(
        element.querySelectorAll(".k-editor-block-gallery-image")
      );

      images[0].focus();
    },
    async focusImage(imageIndex) {
      await this.$nextTick();
      const element = this.$refs.element;

      const images = Array.from(
        element.querySelectorAll(".k-editor-block-gallery-image")
      );

      if (images.length === 0) {
        this.focus({ focusRoot: true });
        return;
      }

      if (imageIndex < 0) {
        images[0].focus();
        return;
      }

      if (imageIndex >= images.length) {
        images[images.length - 1].focus();
        return;
      }

      images[imageIndex].focus();
    },
    getTotalRatio(images) {
      if (!images) return;
      let totalRatio = 0;
      for (let image of images) totalRatio = image.ratio + totalRatio;
      return totalRatio;
    },
    calcImageDimentions(images, image) {
      const totalRatio = this.getTotalRatio(images);
      const imageRatio = image.width / image.height;
      const ratioHeight =
        (image.height / image.width) * (imageRatio / totalRatio) * 100;
      const width = Math.ceil((imageRatio / totalRatio) * 1000) / 10;
      return `position: relative; width: ${width}%; padding-bottom: ${ratioHeight}%;`;
    },
    lazyLoadImages() {
      const observer = lozad(".k-editor-block-gallery-image img");
      observer.observe();
    },
    input(data) {
      this.$emit("input", {
        attrs: {
          ...this.attrs,
          ...data
        }
      });
      this.lazyLoadImages();
    },
    async fetchFile(link) {
      const response = await this.$api.get(link);
      return {
        guid: response.link,
        src: response.url,
        id: response.id,
        filename: response.filename,
        ratio: response.dimensions.ratio,
        width: response.dimensions.width,
        height: response.dimensions.height
      };
    },
    onDragEnd() {
      const images = this.images;
      this.input({
        images
      });
    },
    addMultipleImageRows(images) {
      const sortedImages = images.sort(sortAlphaNum);
      // Batch images
      let batches = [];
      let currentBatch = [];
      let currentBatchRatio = 0;
      const batchRatioLimit = 3.2;

      for (let image of sortedImages) {
        if (
          currentBatch.length === 0 ||
          currentBatchRatio + image.ratio <= batchRatioLimit
        ) {
          currentBatch.push(image);
          currentBatchRatio += image.ratio;
          continue;
        }

        batches.push(currentBatch);
        currentBatch = [image];
        currentBatchRatio = image.ratio;
      }

      if (currentBatch.length > 0) {
        batches.push(currentBatch);
        currentBatch = [];
        currentBatchRatio = 0;
      }

      batches.reverse().map((batch, index) => {
        if (index === batches.length - 1) {
          this.input({
            images: batch
          });
        } else {
          this.$emit("append", {
            type: "gallery",
            attrs: {
              group: "default",
              images: batch
            }
          });
        }
      });
    },
    async insertUpload(files, responses) {
      const uploads = await Promise.all(
        responses.map(response => this.fetchFile(response.link))
      );
      const images = this.images;
      const newImageList = [...images, ...uploads];

      this.addMultipleImageRows(newImageList);
      // this.input({
      //   images: newImageList
      // });

      this.$events.$emit("file.create");
      this.$events.$emit("model.update");
      this.$store.dispatch("notification/success", ":)");
    },
    menu() {
      if (this.images) {
        return [
          {
            icon: "cog",
            label: this.$t("editor.blocks.image.settings"),
            click: this.$refs.settings.open
          },
          {
            icon: "add",
            label: "Add image to this row",
            click: this.selectFiles
          }
        ];
      } else {
        return [];
      }
    },
    onDrop(files) {
      this.$refs.fileUpload.drop(files, {
        url: window.panel.api + "/" + this.endpoints.field + "/upload",
        multiple: true,
        accept: "image/*"
      });
    },
    uploadFiles() {
      this.$refs.fileUpload.open({
        url: window.panel.api + "/" + this.endpoints.field + "/upload",
        multiple: true,
        accept: "image/*"
      });
    },
    replace() {
      this.$emit("input", {
        attrs: {}
      });
    },
    selectFiles() {
      this.$refs.fileDialog.open({
        endpoint: this.endpoints.field + "/files",
        multiple: true,
        selected: []
      });
    },
    async insertFiles(files) {
      const objects = await Promise.all(
        files.map(file => this.fetchFile(file.link))
      );
      const images = this.images;
      const newImages = [...images, ...objects];
      this.addMultipleImageRows(newImages);
    },
    addImageRow() {
      this.$emit("append", {
        type: "gallery",
        attrs: {
          group: "default",
          images: []
        }
      });
    },
    deleteImage(imageIndex) {
      const images = this.images;
      const newImages = [
        ...images.slice(0, imageIndex),
        ...images.slice(imageIndex + 1, images.length)
      ];
      this.input({
        images: newImages
      });
      if (newImages.length === 0) {
        this.focus({ focusRoot: true });
      } else {
        const focusIndex = imageIndex > 0 ? imageIndex - 1 : 0;
        this.focusImage(focusIndex);
      }
    },
    settings() {
      this.$refs.settings.open();
    },
    saveSettings() {
      this.$refs.settings.close();
      this.input(this.attrs);
    }
  }
};
</script>

<style lang="scss">
@import "../variables.scss";

.k-editor-gallery-block {
  position: relative;
  user-select: none;

  &:focus {
    outline: 0;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.5);
  }

  & + .k-editor-gallery-block {
    padding-top: 0px;
    margin-top: -6px;
  }

  .k-editor-gallery-row {
    position: relative;

    .k-editor-gallery-row-empty {
      position: absolute;
      top: 0;
      bottom: 0;
      left: 0;
      right: 0;
      display: flex;
      justify-content: center;
      align-content: center;
      background-color: rgba($color: #000000, $alpha: 0.1);

      .separator {
        font-style: italic;
        color: #888;
        font-size: $font-size-small;
        margin-left: 1rem;
        margin-right: 1rem;
      }

      button {
        span {
          margin-left: 0.1rem;
        }
      }

      span {
        display: inline-flex;
        align-items: center;
      }
    }

    .k-draggable {
      display: flex;

      &.empty-draggable {
        padding: 20px;
      }

      .k-editor-row-image {
        position: relative;

        img {
          display: block;
          position: absolute;
          height: 100%;
          width: 100%;
          padding: 1px;
          top: 0;
          left: 0;
        }

        &.ghost {
          opacity: 0;
        }
      }
    }
  }
}

.k-editor-row-image.sortable-chosen.sortable-drag {
  position: relative;
  img {
    width: 100%;
    opacity: 0.5;
  }
}

.k-editor-row-image.sortable-chosen.k-sortable-ghost {
  img {
    object-fit: contain;
  }
}

.k-editor-gallery-block .k-editable-placeholder,
.k-editor-gallery-block .ProseMirror {
  text-align: center;
  font-size: 0.875rem;
  line-height: 1.5em;
}
</style>
