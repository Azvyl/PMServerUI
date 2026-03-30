# PMServerUI

PMServerUI is a simple library for [PocketMine-MP](https://github.com/pmmp/PocketMine-MP) that helps you to create forms
for your server. It takes the familiar [**FormAPI**](https://github.com/jojoe77777/FormAPI) and upgrades it with the
features from the **Minecraft Bedrock Script API** ([
`minecraft/server-ui`](https://learn.microsoft.com/en-us/minecraft/creator/scriptapi/minecraft/server-ui/minecraft-server-ui?view=minecraft-bedrock-stable)).

The original FormAPI was good, but it used older UI elements. PMServerUI changes that! We've basically combined the
simplicity of FormAPI with the modern UI abilities of the Bedrock API.

## Why Use PMServerUI?

* **Modern UI:** Access powerful UI elements similar to those used in Bedrock Add-ons.
* **Reliable Data Handling:** Know if a bad client sends malformed data, helping protect your plugin from incorrect
  inputs.
* **Player Disconnect Detection:** Easily detect if a player leaves the server before submitting a form, allowing you to
  handle these situations gracefully.
* **Localization Support:** Supports translations based on the player's language and resource pack.

---

## How to Use This Virion (For Developers)

As a [**virion**](https://poggit.pmmp.io/virion), PMServerUI is not a plugin you install directly. Instead, it's a
library that other plugins *include* in their code.

### Including PMServerUI in Your Plugin

- Using **Composer** (virion v3):

  Add this as a dependency to your Composer project. Open your terminal in your project's root directory and run the
  following command:

  ```bash
  composer require azvyl/pmserver-ui
  ```

  After running the command, your `composer.json` file will be updated. You can inspect it to see the new dependency
  listed, similar to this:

  ```json5
  {
    "require": {
      // other dependencies ...
      "azvyl/pmserver-ui": "^2.0"
    }
  }
  ```


- Using **Poggit CI** (virion v1):

  Add this to your `.poggit.yml` file:

   ```yaml
   projects:
     YourPlugin:
       libs:
         - src: Azvyl/PMServerUI/PMServerUI
           version: ^2.0.0
   ```

---

## Example Usage (For Developers)

See plugin example [here](https://github.com/Azvyl/PMServerUI-Example)

---

## License

This project is licensed under the MIT License - see the `LICENSE` file for details.

---