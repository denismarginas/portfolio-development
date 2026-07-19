# Workspace Setup Guide

This document contains everything needed to configure the development environment for this project, including prerequisites, required extensions, and automated workspace settings.

## 1. Prerequisites

- **Editor:** [Visual Studio Code](https://code.visualstudio.com/)

## 2. Automatic Configuration Files

To apply these settings automatically when the project opens, create a `.vscode` folder at the root of your project and add the following two files.

### File 1: `.vscode/extensions.json`

This file prompts anyone opening the project to install the required formatting extensions.

```json
{
  "recommendations": ["esbenp.prettier-vscode", "j-brooke.fracturedjsonvsc"]
}
```

### File 2: `.vscode/settings.json`

This file configures Prettier for general formatting and applies the exact custom rules for FracturedJSON.

```json
{
  "editor.formatOnSave": true,
  "editor.defaultFormatter": "esbenp.prettier-vscode",
  "[json]": {
    "editor.defaultFormatter": "j-brooke.fracturedjsonvsc"
  },
  "[jsonc]": {
    "editor.defaultFormatter": "j-brooke.fracturedjsonvsc"
  },
  "fracturedjsonvsc.maxTotalLineLength": 180,
  "fracturedjsonvsc.maxInlineComplexity": 4,
  "fracturedjsonvsc.maxCompactArrayComplexity": 3,
  "fracturedjsonvsc.maxTableRowComplexity": 3,
  "fracturedjsonvsc.maxPropNamePadding": 16,
  "fracturedjsonvsc.tableCommaPlacement": "BeforePaddingExceptNumbers",
  "fracturedjsonvsc.minCompactArrayRowItems": 3
}
```

## 3. How to Use

1. Place the files above inside your project's `.vscode/` directory.
2. Open the project folder in VS Code.
3. When the notification popup appears in the bottom-right corner asking to install recommended extensions, click **Install All**.
4. Files will now format automatically according to these rules every time you save.
