# Uptime Kuma Incidents

## ✨ About

### Uptime Kuma Incidents app for Nextcloud

**Manage [Uptime Kuma](https://github.com/louislam/uptime-kuma) instances and incidents directly from the Nextcloud administration interface.**

- 🛡️ **Admin Only:** Exclusively accessible for system administrators.
- 🚦 **Incident Management:** Trigger incidents directly from Nextcloud with custom styles (info, warning, success, danger).
- 🔄 **Lifecycle Control:** Resolve ongoing incidents or simulate service failures with a single click.
- 🔑 **Token-Based API:** Secure REST endpoint for external scripts and automation workflows.
- 🎯 **Granular Access:** Assign individual API tokens to specific jobs for fine-grained authorization.
- 🤖 **Script Automation:** Easily trigger status updates automatically from external systems.

## ⚙️ Usage

- It is recommended to download or install this app directly from the [Nextcloud App store](https://apps.nextcloud.com/apps/uptimekuma).
- Alternatively you can download the [latest uptimekuma release](https://github.com/zomtec2311/uptimekuma/releases) based on this repository.

To get started follow the instructions.

## 🚀 Instructions

### Setup

1. Under **Kuma instances**, create an Uptime Kuma instance with URL, username, and password.
2. Click **Test connection** to verify that the authentication works.
3. Create a job under **Jobs**. A job links a Kuma instance to a status page (`statusSlug`) and defines the title, text, and default style of the incident.
4. Open **API token** on the job and generate a token. The token belongs to the job and is intended for external scripts (not for Uptime Kuma itself).
5. The token is displayed in full only once. The three generated API URLs can be copied directly.
6. The external script calls one of the following endpoints via POST depending on the execution state:
   - `/start` at the beginning of a task
   - `/failed` in case of an error
   - `/resolve` after successful completion

### Behavior

- `start` creates an incident if no open incident exists for the job.
- Re-running `start` continues to use the currently open incident without generating duplicates.
- `failed` sets the open incident status to `danger` and leaves it open.
- `resolve` resolves an active or failed incident.
- A subsequent successful run can thus automatically resolve a previous error.

### Manual Test Actions

Under **Test actions**, each job provides three manual actions: Start, Failed, and Resolve. These serve solely for testing the reporting logic. The actual backup or external process runs independently outside of this app.

## 💡 F.A.Q.

<details>
  <summary><b>All of the text is in english?</b></summary>
	Maybe your language files are missing.

  You might want to help translating the app to new languages or report errors in existing translations. So feel free and send me translations.
</details>

<details>
  <summary><b>Very bad translation?</b></summary>
  We used the AI-based Libretranslate to generate language files. Of course, there were limitations to the translation depending on the quality of the AI. If you'd like to help improve your language file, open an issue and report your suggestion for improvement. Thank you
</details>

## 🤝 How you can support this project

1. **🌟 Star this repository**: This is the easiest way to support UptimeKuma and it costs nothing.
2. **⭐ Rate and/or 💬 comment** on UptimeKuma in the [ Nextcloud AppStore](https://apps.nextcloud.com/apps/uptimekuma)
3. **🪲 Report bugs**: Report any bugs you find on the issue tracker.
4. **📖 Translate**: Help translate UptimeKuma into your language, if the AI-based Libretranslate generated language files are poorly translated
5. **📝 Contribute**: Read and file or comment on an issue and ask for guidance or give advice.

## 🌍 How to Contribute Translations (Step-by-Step Guide)

We love contributions! If you notice a missing or incorrect translation, you can easily fix it directly on GitHub without installing any specialized software.

Follow these simple steps to make your changes and send us a **Pull Request (PR)**:

---

### Step 1: Fork the Repository
A **Fork** creates your own copy of this project under your GitHub account, allowing you to make changes safely.

1. Scroll to the very top right of this GitHub page.
2. Click the **Fork** button (located near the *Star* button).
3. Click **Create fork**. You will now be redirected to your own copy of the repository.

---

### Step 2: Locate and Edit the Translation File
1. In your forked repository, navigate to the folder containing the language files (e.g., `l10n/`).
2. Click on the file for your language (for example, `de.json` or `uptimekuma_de.po`).
3. Click the **Pencil icon** (✏️) in the top-right corner of the file view to open the editor.
4. Edit or add the missing translations carefully:
   * **JSON files:** Keep the quotes `""` and commas `,` intact.
   * **PO files:** Add your translation inside the `msgstr ""` quotes right below the matching `msgid ""`.

---

### Step 3: Commit Your Changes
Once you have made your edits:

1. Click the green **Commit changes...** button at the top right of the editor.
2. Enter a short description of what you changed (e.g., `Fix German translation for plain text hint`).
3. Make sure **Commit directly to the `main` branch** (or `master`) is selected.
4. Click **Commit changes**.

---

### Step 4: Submit Your Pull Request (PR)
Now send your changes back to us so we can review and include them in the official release!

1. Go back to the main page of **your forked repository**.
2. You should see a banner at the top showing your branch is ahead. Click **Contribute**, then click **Open pull request**.
   *(If you don't see the banner, click the **Pull requests** tab, then the green **New pull request** button).*
3. Double-check your changes on the comparison screen.
4. Click **Create pull request**.
5. Add a brief title and description, then click **Create pull request** once more to finalize.

---

🎉 **That's it!** Thank you for helping improve the project. We will review your contribution as soon as possible and merge it into the main repository.
