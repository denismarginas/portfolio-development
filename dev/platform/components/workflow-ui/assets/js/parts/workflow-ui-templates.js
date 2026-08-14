function applyTemplate(templateName) {
  const fields = cardForm.elements;

  if (templateName === "database") {
    fields["title"].value = "DataBase";
    fields["type"].value = "database";
    fields["action"].value = "loadJsonFile";
    fields["inputs"].value = "";
    fields["outputs"].value = "data:Data, count:Count";
    fields["variables"].value = "source:";
  } else if (templateName === "selectfile") {
    fields["title"].value = "Select File";
    fields["type"].value = "selectfile";
    fields["action"].value = "filterByFile";
    fields["inputs"].value = "data:Data";
    fields["outputs"].value = "items:Items";
    fields["variables"].value = "file:,post_type:";
  } else if (templateName === "structure") {
    fields["title"].value = "Structure";
    fields["type"].value = "structure";
    fields["action"].value = "";
    fields["inputs"].value = "data:Data";
    fields["outputs"].value = "items:Items";
    fields["variables"].value = "";
  } else if (templateName === "project_structure") {
    fields["title"].value = "Project Structure";
    fields["type"].value = "project_structure";
    fields["action"].value = "";
    fields["inputs"].value = "items:Items";
    fields["outputs"].value = "config:Config";
    fields["variables"].value = "header:header, footer:footer, page_structure:page_constructor, body_wrapper:";
  } else if (templateName === "page_structure") {
    fields["title"].value = "Page Structure";
    fields["type"].value = "page_structure";
    fields["action"].value = "";
    fields["inputs"].value = "items:Items";
    fields["outputs"].value = "config:Config";
    fields["variables"].value = "header:header, footer:footer, page_structure:page_constructor";
  } else if (templateName === "seo_project") {
    fields["title"].value = "Seo Projects";
    fields["type"].value = "seo_project";
    fields["action"].value = "";
    fields["inputs"].value = "data:Data";
    fields["outputs"].value = "seo:SEO";
    fields["variables"].value = "title_max:50, description_max:140, index:index, keywords_source:_id";
  } else if (templateName === "seo_page") {
    fields["title"].value = "Seo Page";
    fields["type"].value = "seo_page";
    fields["action"].value = "";
    fields["inputs"].value = "data:Data";
    fields["outputs"].value = "seo:SEO";
    fields["variables"].value = "index:index";
  } else if (templateName === "render") {
    fields["title"].value = "Preview Render";
    fields["type"].value = "render";
    fields["action"].value = "renderPage";
    fields["inputs"].value = "items:Items, config:Config, seo:SEO, compile_scss:SCSS Config";
    fields["outputs"].value = "";
    fields["variables"].value = "debug_post_data:false, compile_assets:false";
  } else if (templateName === "compile_scss") {
    fields["title"].value = "Compile SCSS";
    fields["type"].value = "compile_scss";
    fields["action"].value = "compileScss";
    fields["inputs"].value = "";
    fields["outputs"].value = "config:Config";
    fields["variables"].value = "compile_scss_platform_components:true, compile_scss_src_components:true, compile_scss_assets:false, compile_scss_everytime:true";
  } else if (templateName === "live_preview" || templateName === "translation") {
    fields["title"].value = templateName === "live_preview" ? "Live Preview" : "Translation";
    fields["type"].value = templateName;
    fields["action"].value = templateName === "live_preview" ? "viewPreview" : "translateData";
    fields["inputs"].value = "";
    fields["outputs"].value = "config:Config";
    fields["variables"].value = templateName === "live_preview" ? "post_type:page, _id:home" : "";
  } else {
    cardForm.reset();
    fields["title"].focus();
  }
}

function updateCardFromInspector() {
  const card = getCard(selectedCardId);
  if (!card) return;

  const fields = inspectorElement.querySelectorAll("[data-inspector-field]");
  fields.forEach((field) => {
    const name = field.dataset.inspectorField;
    const value = field.value.trim();

    if (name === "inputs") {
      card.inputs = normalizePorts(value);
    } else if (name === "outputs") {
      card.outputs = normalizePorts(value);
    } else if (name === "variables") {
      card.variables = splitList(value).map((entry) => {
        const [n, ...rest] = entry.split(":").map((part) => part.trim());
        return { name: n, value: rest.join(":") || "" };
      }).filter((v) => v.name);
    } else if (name === "title" || name === "type" || name === "action" || name === "note") {
      card[name] = value;
    }
  });

  renderAll();
  queueSave();
  setStatus(t("canvas.saved"));
}
