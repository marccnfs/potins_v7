import tinymce from "tinymce";
import 'tinymce/themes/silver';
import 'tinymce/icons/default/icons';
import 'tinymce/plugins/code';
import 'tinymce/models/dom/model';
import 'tinymce/plugins/image';
import 'tinymce/langs/fr_FR';

tinymce.init({
    selector: '#mytextarea',
    menubar: false,
    toolbar: 'styleselect bold italic alignleft aligncenter alignright bullist indent code image',
    plugins: ['code','image'],
    language:  'fr_FR',
});
let incontenthtml=postar.data('content');
let med = tinymce.get('mytextarea');

if(incontenthtml!==""){
    // med.load()incontenthtml);
    med.selection.setContent(incontenthtml)
}