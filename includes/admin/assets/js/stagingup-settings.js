jQuery(document).ready(function($) {
    $('.color-field').each(function() {
        $(this).wpColorPicker();
    });
});

function update(text) {
    let result_element = document.querySelector("#highlighting-content");
    // Handle final newlines (see article)
    if(text[text.length-1] == "\n") {
        text += " ";
    }
    // Update code
    result_element.innerHTML = text;
    //result_element.innerHTML = text.replace(new RegExp("&", "g"), "&amp;").replace(new RegExp("<", "g"), "&lt;").replace(new RegExp(">", "g"), "&gt;"); /* Global RegExp */
    // Syntax Highlight
    Prism.highlightElement(result_element);
}

function sync_scroll(element) {
    /* Scroll result to scroll coords of event - sync with textarea */
    let result_element = document.querySelector("#highlighting");
    // Get and set x and y
    result_element.scrollTop = element.scrollTop;
    result_element.scrollLeft = element.scrollLeft;
}

function check_tab(element, event) {
    let code = element.value;
    if(event.key == "Tab") {
        /* Tab key pressed */
        event.preventDefault(); // stop normal
        let before_tab = code.slice(0, element.selectionStart); // text before tab
        let after_tab = code.slice(element.selectionEnd, element.value.length); // text after tab
        let cursor_pos = element.selectionEnd + 1; // where cursor moves after tab - moving forward by 1 char to after tab
        element.value = before_tab + "\t" + after_tab; // add tab char
        // move cursor
        element.selectionStart = cursor_pos;
        element.selectionEnd = cursor_pos;
        update(element.value); // Update text to include indent
    }
}

jQuery(document).ready(function($) {

    // Import
    let loginSettingsImportUploader = document.getElementById('stagingup-settings-import-uploader');

    let btnImport = document.getElementById('stagingup-settings-import');
    btnImport.addEventListener('click', function() {
        let status = document.getElementById('stagingup-settings-import-uploader-status');
        status.innerHTML = 'Importing...';

        function readAsText(blob, callback) {
            var reader = new FileReader();
            reader.onloadend = function() {
                callback(reader.result);
            }
            reader.readAsText(blob);
        }

        if (loginSettingsImportUploader.files && loginSettingsImportUploader.files[0]) {
            readAsText(loginSettingsImportUploader.files[0], function(text) {
                console.log('RESULT:', JSON.parse(text))

                console.log('SENDING: ', JSON.stringify(JSON.parse(text)));

                $.ajax({
                    type: 'post',
                    url: localized_data.ajax_url + ( localized_data.ajax_url.indexOf( '?' ) > 0 ? '&' : '?' ) + 'action=stagingup_import_settings',
                    data: {
                        nonce: localized_data.stagingup_import_settings_nonce,
                        options: JSON.stringify(JSON.parse(text))
                    },
                    success: (response) => {
                        console.log('IMPORT RESPONSE: ', response);
                        status.innerHTML = 'Imported!  Please refresh the page.';
                    },
                    dataType: 'json'
                });
            });
        }
    });


    // Export
    function download(content, fileName, contentType) {
        var a = document.createElement("a");
        var file = new Blob([content], {type: contentType});
        a.href = URL.createObjectURL(file);
        a.download = fileName;
        a.click();
    }
    let btnExport = document.getElementById('stagingup-settings-export');
    btnExport.addEventListener('click', function() {
        let status = document.getElementById('stagingup-settings-export-uploader-status');
        status.innerHTML = 'Exporting...';

        $.ajax({
            type: 'post',
            url: localized_data.ajax_url + ( localized_data.ajax_url.indexOf( '?' ) > 0 ? '&' : '?' ) + 'action=stagingup_export_settings',
            data: {
                nonce: localized_data.stagingup_export_settings_nonce
            },
            success: (response) => {
                download(JSON.stringify(response.data, null, 4), 'staging-upseller.json', 'application/json');
                status.innerHTML = 'Exporting...';
            },
            dataType: 'json'
        });
    });


    // Logo uploader
    function toDataURL(blob, callback) {
        var reader = new FileReader();
        reader.onloadend = function() {
            callback(reader.result);
        }
        reader.readAsDataURL(blob);
    }
    
    // Logo upload
    let loginLogoUploader = document.getElementById('login-logo-uploader');
    loginLogoUploader.addEventListener('change', function() {
        console.log('changed');
        if (this.files && this.files[0]) {
            toDataURL(this.files[0], function(dataUrl) {
                let logoInput = document.getElementsByName('staging_upseller[login_logo]')[0];
                logoInput.value = dataUrl;

                let preview = document.getElementsByClassName('stagingup-image-upload-preview')[0];
                preview.src = dataUrl;
            });
        }
    });



    // Layout configs

    // 0 1 2
    // 3 4 5
    // 6 7 8

    let layoutAreas = [];

    for ( let i = 0; i < 9; i++ ) {
        layoutAreas.push( document.getElementById( 'login_layout_area_' + i ) );
    }
    console.log(layoutAreas);

    let sLeftAA1Right = document.getElementById('btn-sidebar-left-ad-area-1-right');
    sLeftAA1Right.addEventListener('click', () => {
        layoutAreas[0].value = 'login_sidebar';
        layoutAreas[3].value = 'login_sidebar';
        layoutAreas[6].value = 'login_sidebar';

        layoutAreas[1].value = 'ad_area_0';
        layoutAreas[2].value = 'ad_area_0';
        layoutAreas[4].value = 'ad_area_0';
        layoutAreas[5].value = 'ad_area_0';
        layoutAreas[7].value = 'ad_area_0';
        layoutAreas[8].value = 'ad_area_0';
    } );

    let sRightAA1Left = document.getElementById('btn-sidebar-right-ad-area-1-left');
    sRightAA1Left.addEventListener('click', () => {
        layoutAreas[0].value = 'ad_area_0';
        layoutAreas[3].value = 'ad_area_0';
        layoutAreas[6].value = 'ad_area_0';
        layoutAreas[1].value = 'ad_area_0';
        layoutAreas[4].value = 'ad_area_0';
        layoutAreas[7].value = 'ad_area_0';

        layoutAreas[2].value = 'login_sidebar';
        layoutAreas[5].value = 'login_sidebar';
        layoutAreas[8].value = 'login_sidebar';
    } );

    let sCenterAA1LeftAA2Right = document.getElementById('btn-sidebar-center-ad-area-1-left-ad-area-2-right');
    sCenterAA1LeftAA2Right.addEventListener('click', () => {
        layoutAreas[0].value = 'ad_area_0';
        layoutAreas[3].value = 'ad_area_0';
        layoutAreas[6].value = 'ad_area_0';
        layoutAreas[1].value = 'login_sidebar';
        layoutAreas[4].value = 'login_sidebar';
        layoutAreas[7].value = 'login_sidebar';
        layoutAreas[2].value = 'ad_area_1';
        layoutAreas[5].value = 'ad_area_1';
        layoutAreas[8].value = 'ad_area_1';
    } );



    let editing = document.getElementById('editing');
    update(editing.innerHTML);
});