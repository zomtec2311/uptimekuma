# Changelog

## 1.0.5

## 1.0.4

### Fixed
- Typo in HistoryCleanupService caused a 500 server error

## 1.0.3

### Added
- new settings
- Instance is deleted -> related jobs are also deleted
- Job is deleted -> related tokens are also deleted -> Jobs will also be deleted from history
- 24 hourly background job to automatically delete old entries in the history db. Age of the Jobs to be deleted is adjustable in the settings

### Changed
- **l10n:** Language files extended by additional phrases

## 1.0.2

### Changed
-  Previously executed jobs: correct pagination instead of the 2 buttons previous and next
- Previously executed jobs: Abbreviated error text. Complete text at hover
- **l10n:** Language files extended by additional phrases

## 1.0.1

### Changed
- Conversion of the app messages to Nextcloud dialogs (showSuccess, showError)
- **l10n:** Language files extended by additional phrases

### Added
- In a new settings area, there is the first parameter for the entries per page for "Previously executed jobs"

## 1.0.0

### First Release
