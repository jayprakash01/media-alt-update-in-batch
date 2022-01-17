# Media alt and title update in batch process

The media alt update module update the image ALT and Title of the Media entity
in a batch. In case of empty alt, updating the alt and title with name field of
the media entity in a bulk.

### Features of the Module
* It will update the empty alt with media's name field in batch process.
* It requires Media module that must be enabled and having data with empty Alt
and Title text.

### Installation Steps

1. Enable the module `media_alt_update` from Extends.
2. Enable the module - Media.

### How to run the batch process
Go to the configuration -> Media Alt Update (/admin/config/media-alt-update)
Click on `Update` button to start the update.
