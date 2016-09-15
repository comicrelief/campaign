# CR Content Wall module

This module contains exported configuration including custom block content types,
view modes, image styles and a custom Display Suite field that contains custom
logic to render child blocks in specific view modes.

## CW Row block

Row block is a custom block type that contains a

|field_name|type|comment|
|--- | --- | --- | --- | --- |
|`field_cw_block_reference`|`entity_reference`|Field used to reference block content|
|`field_cwrowdisplay`|`DsField`|Custom DsField used to render referenced blocks|

Row blocks are used to display a set number of content blocks within a row.
This is done using a combination of view modes and custom logic. The entity reference
field is hidden from display and depending on the view mode select to render the
row, the logic decides what view mode to use when rendering each individual child block.

## CW Node reference block

@TODO
