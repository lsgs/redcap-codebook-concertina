<?php
/**
 * REDCap External Module: Codebook Concertina
 * Expand and collapse the codebook table rows for the fields of each instrument
 * @author Luke Stevens, Murdoch Children's Research Institute
 */
namespace MCRI\CodebookConcertina;

use ExternalModules\AbstractExternalModule;
use REDCap;

class CodebookConcertina extends AbstractExternalModule
{
        const DEFAULT_TEXT_SHOW = 'Expand';
        const DEFAULT_TEXT_SHOW_ALL = 'Expand all instruments';
        const DEFAULT_TEXT_HIDE = 'Collapse';
        const DEFAULT_TEXT_HIDE_ALL = 'Collapse all instruments';
        const DEFAULT_VISIBILITY = '1';
        
        public function redcap_every_page_top($project_id) {
                if (isset($project_id) && intval($project_id)>0 && PAGE==='Design/data_dictionary_codebook.php') {
                        $btnTextShow = $this->getShowButtonText();
                        $btnTextShowAll = $this->getShowAllButtonText();
                        $btnTextHide = $this->getHideButtonText();
                        $btnTextHideAll = $this->getHideAllButtonText();
                        $defaultVisibility = $this->getDefaultVisibility();
                        // add js to page to add toggle buttons and hide/show
                        ?>
<script type='text/javascript'>
    (function(window, document, $) {
        $(document).ready(function() {
            var defaultVisibility = <?php echo $defaultVisibility;?>;
            var icons = ['down', 'up'];
            var btnLbl = ['<?php echo $btnTextShow;?>', '<?php echo $btnTextHide;?>'];
            var btnLblAll = ['<?php echo $btnTextShowAll;?>', '<?php echo $btnTextHideAll;?>'];
            var currentForm = '';

            function btnLblText(visibility) {
                return '<span class="glyphicon glyphicon-chevron-'+icons[visibility]+'"></span>&nbsp;'+btnLbl[visibility];
            }

            function btnLblAllText(visibility) {
                return '<span class="glyphicon glyphicon-chevron-'+icons[visibility]+'"></span>&nbsp;'+btnLblAll[visibility];
            }
            
            var toggleRows = function() {
                var $this = $(this);
                var toggleForm = this.id;
                var visible = btnLbl.indexOf($this.text().trim()); // visible when button says "Collapse"
                if (visible) {
                    // collapse and switch button lbl to "Expand" when expanded
                    $this.html(btnLblText(0)); 
                    $('table.ReportTableWithBorder tr.'+toggleForm).hide();
                } else {
                    // expand and switch button lbl to "Collapse" when collapsed
                    $this.html(btnLblText(1)); 
                    $('table.ReportTableWithBorder tr.'+toggleForm).show();
                }
            };

            var toggleAllRows = function() {
                var $this = $(this);
                var toggleType = btnLblAll.indexOf($this.text().trim());
                // trigger click on all buttons with text corresponding to the visibility e.g. if Collapse all, all the Collapse buttons
                $('table.ReportTableWithBorder button.toggle-rows:contains("'+btnLbl[toggleType]+'")').trigger('click');
                $this.html(btnLblAllText((toggleType)?0:1));
            };
            
            $('table.ReportTableWithBorder:first > tbody > tr').each(function() { // main table.ReportTableWithBorder trs only - ignore table.ReportTableWithBorder subtables for sql fields
                var rowTDs = $(this).find('td');
                if (rowTDs.length===0) {
                    // this is the th row - do nothing
                } else if (rowTDs.length===1) {
                    // this is a form header row
                    //  - extract the form ref from the final span (<span style="margin-left:10px;color:#444;">(instrument_name)</span>
                    //  - add toggle button
                    currentForm = $(rowTDs[0]).find('span').last().html().replace('(','').replace(')','');
                    $('<button type="button" id="toggle-'+currentForm+'" class="btn btn-xs btn-primary toggle-rows" style="float:right;" data-toggle="button">'+btnLblText(defaultVisibility)+'</button>')
                            .on('click', toggleRows)
                            .appendTo(rowTDs[0]);
                } else {
                    // this is a variable's tr
                    //  - add a class to target with the toggle
                    //  - hide if default is hidden
                    $(this).addClass("toggle-"+currentForm);
                    if (!defaultVisibility) { $(this).hide(); }
                }
            });

            $('<button type="button" id="toggle-all-forms" class="btn btn-xs btn-primary" style="float:right;margin:5px;" data-toggle="button">'+btnLblAllText(defaultVisibility)+'</button>')
                    .on('click', toggleAllRows)
                    .insertBefore('table.ReportTableWithBorder:first');
        });
    })(window, document, jQuery);
</script>
                        <?php
                }
        }
        
        protected function getDefaultVisibility() {
                $defaultVisibility = $this->getProjectSetting('default-visibility');
                if ($defaultVisibility!='0' && $defaultVisibility!='1') {
                        $defaultVisibility = self::DEFAULT_VISIBILITY;
                        $this->setProjectSetting('default-visibility', $defaultVisibility);
                }
                return $defaultVisibility;
        }
        
        protected function getShowButtonText() {
                return $this->getButtonText('button-text-show', self::DEFAULT_TEXT_SHOW);
        }
        
        protected function getShowAllButtonText() {
                return $this->getButtonText('button-text-show-all', self::DEFAULT_TEXT_SHOW_ALL);
        }
        
        protected function getHideButtonText() {
                return $this->getButtonText('button-text-hide', self::DEFAULT_TEXT_HIDE);
        }
        
        protected function getHideAllButtonText() {
                return $this->getButtonText('button-text-hide-all', self::DEFAULT_TEXT_HIDE_ALL);
        }
        
        protected function getButtonText($setting, $default) {
                $btnText = $this->getProjectSetting($setting);

                if ($btnText=='') {
                        $this->setProjectSetting($setting, $default);
                        $btnText = $default;
                }
                return REDCap::escapeHtml($btnText);
        }
}