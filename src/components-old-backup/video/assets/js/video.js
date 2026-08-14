class Video {
  static TEMPLATE_URL = 'components/video/assets/html/template.html';
  static #compile(templateStr, data) {
    return templateStr.replace(/\{\{\s*([\w.]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : "";
    });
  }

  static async render(container, data, templateUrl = Video.TEMPLATE_URL) {
    const targetElement =
      typeof container === "string"
        ? document.querySelector(container)
        : container;
    if (!targetElement) return null;

    try {
      const response = await fetch(templateUrl);
      if (!response.ok) return null;
      let html = await response.text();

      let thumbnailSection = "";
      if (data.thumbnail) {
        const match = html.match(
          /<template id="video-thumbnail-template">([\s\S]*?)<\/template>/,
        );
        if (match && match[1]) {
          const bgStyle = data.thumbnail_bg
            ? `style="background-image: url('${data.thumbnail_bg}')"`
            : "";
          const imageHtml = `<img src="${data.thumbnail}" alt="Thumbnail" />`;

          thumbnailSection = this.#compile(match[1], {
            thumbnail_bg_style: bgStyle,
            thumbnail_image: imageHtml,
            play_svg: data.play_svg || "",
            pause_svg: data.pause_svg || "",
          });
        }
      }

      html = html.replace(
        /<template id="video-thumbnail-template">[\s\S]*?<\/template>/,
        "",
      );

      targetElement.innerHTML = this.#compile(html, {
        src: data.src || "",
        thumbnail_section: thumbnailSection,
      });

      return targetElement.querySelector(".video-container");
    } catch (error) {
      console.error(error);
      return null;
    }
  }
}

window.Video = window.Video || Video;
