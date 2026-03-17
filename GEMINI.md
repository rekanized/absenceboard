# Project Guidelines

## Tech Stack & Dependencies
* **Dependencies:** Use **Composer** ONLY. Do NOT use NPM for any package management or build steps.
* **Icons:** Material Symbols Icons (uses the class `.icon` and then the icon name is within the element).
* **IDE:** Visual Studio Code.
* **Frontend:** Alpine.js or vanilla JS. Avoid jQuery.

## Coding Standards
* **CSS Strategy:** Build reusable Vanilla CSS classes and structure the files in a way that is easy to maintain and understand.
* **No Frameworks:** Do NOT use Tailwind CSS or Bootstrap.

## Livewire Components
* **Creation:** ALWAYS use `php artisan make:livewire` and let it create the files, then edit them. 
* **Manual Warning:** Do NOT trust the `php artisan make:livewire` command's console output for `write_to_file`. Sometimes Laravel outputs a ⚡ (lightning bolt) which gets misconstrued as part of the filename. If you must use `write_to_file`, construct the proper path (`app/Livewire/...` and `resources/views/livewire/...`) manually.

## Restrictions
* **No Browser:** Never open a browser to view the project.