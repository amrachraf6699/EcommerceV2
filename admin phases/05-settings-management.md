# Phase 5: Settings Management

## Goal
Turn the `settings` table into a usable settings panel grouped by business meaning.

## Deliverables
- Settings index grouped by:
  - social
  - brand
  - mail
  - marketing
- Dynamic field rendering by `input_type`
- Save/update settings UI
- FilePond integration for file/image settings
- Settings helper/service for reading values consistently

## UX requirements
- Group settings visually and keep each group small
- Use helper text for every non-obvious setting
- File fields must preview current uploaded asset when relevant
- Boolean settings should use friendly toggles, not raw text

## Technical tasks
- Build settings repository/service layer
- Render Blade form components based on type
- Support:
  - text
  - number
  - textarea
  - boolean
  - email
  - password
  - select
  - file
- Save values safely and validate by type

## Tests
- Settings page renders all seeded groups
- Text settings save correctly
- Boolean settings save correctly
- Mail settings validate properly
- File settings upload/store correctly
- Unauthorized admin cannot update settings

## Done when
- Non-technical admins can manage site identity, social links, marketing toggles, and mail configuration from one place
