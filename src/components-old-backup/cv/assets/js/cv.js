class Cv {
  static TEMPLATE_URL = 'components/cv/assets/html/template.html';
  static render(data) {
    const container = data.container || document.body;
    const items = data.items || [];

    items.forEach(item => {
      const card = document.createElement("div");
      card.className = "dm-cv-card";
      card.dataset.motion = "transition-fade-0";
      card.dataset.duration = "0.5s";

      if (item.image) {
        const img = document.createElement("img");
        img.src = item.image;
        img.alt = item.title || "CV";
        img.className = "dm-cv-preview";
        card.appendChild(img);
      }

      const info = document.createElement("div");
      info.className = "dm-cv-info";

      const title = document.createElement("h3");
      title.className = "dm-cv-title";
      title.textContent = item.title || item.name;
      info.appendChild(title);

      if (item.description) {
        const desc = document.createElement("p");
        desc.className = "dm-cv-description";
        desc.textContent = item.description;
        info.appendChild(desc);
      }

      if (item.pdf) {
        const link = document.createElement("a");
        link.className = "dm-cv-download";
        link.href = item.pdf;
        link.target = "_blank";
        link.dataset.button = "primary";
        link.textContent = "Download PDF";
        info.appendChild(link);
      }

      card.appendChild(info);
      container.appendChild(card);
    });
  }
}

window.Cv = window.Cv || Cv;
