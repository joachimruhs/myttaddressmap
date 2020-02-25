


plugin.tx_myttaddressmap {
  view {
    templateRootPaths.0 = EXT:myttaddressmap/Resources/Private/Templates/
    templateRootPaths.1 = {$plugin.tx_myttaddressmap.view.templateRootPath}
    partialRootPaths.0 = EXT:myttaddressmap/Resources/Private/Partials/
    partialRootPaths.1 = {$plugin.tx_myttaddressmap.view.partialRootPath}
    layoutRootPaths.0 = EXT:myttaddressmap/Resources/Private/Layouts/
    layoutRootPaths.1 = {$plugin.tx_myttaddressmap.view.layoutRootPath}
  }
  persistence {
    storagePid = {$plugin.tx_myttaddressmap.persistence.storagePid}
    #recursive = 1

 # only needed for TYPO3 9.x
       classes{

            WSR\Myttaddressmap\Domain\Model\Address {
                mapping {
                    tableName = tt_address
					recordType >


                }
            }

			WSR\Myttaddressmap\Domain\Model\Category {
			  mapping {
				tableName = sys_category
				columns {

				}
			  }
			}

    	}

  }

  features {
    #skipDefaultArguments = 1
	requireCHashArgumentForActionArguments = 0		
  }




  mvc {
    #callDefaultActionIfActionCantBeResolved = 1
  }


	settings {
		defaultIcon = {$plugin.tx_myttaddressmap.settings.defaultIcon}

		googleBrowserApiKey = {$plugin.tx_myttaddressmap.settings.googleBrowserApiKey}
		googleServerApiKey = {$plugin.tx_myttaddressmap.settings.googleServerApiKey}
	
		enableTrafficLayer = {$plugin.tx_myttaddressmap.settings.enableTrafficLayer}
		enableBicyclingLayer = {$plugin.tx_myttaddressmap.settings.enableBicyclingLayer}

		resultPageId = {$plugin.tx_myttaddressmap.settings.resultPageId}
		detailsPageId = {$plugin.tx_myttaddressmap.settings.detailsPageId}
		singleViewUid = {$plugin.tx_myttaddressmap.settings.singleViewUid}

		resultLimit = {$plugin.tx_myttaddressmap.settings.resultLimit}
		initialMapCoordinates = {$plugin.tx_myttaddressmap.settings.initialMapCoordinates}

		mapTheme = {$plugin.tx_myttaddressmap.settings.mapTheme}
	}



}

plugin.tx_myttaddressmap._CSS_DEFAULT_STYLE (
    textarea.f3-form-error {
        background-color:#FF9F9F;
        border: 1px #FF0000 solid;
    }

    input.f3-form-error {
        background-color:#FF9F9F;
        border: 1px #FF0000 solid;
    }

    .typo3-messages .message-error {
        color:red;
    }

    .typo3-messages .message-ok {
        color:green;
    }
)

page.includeCSS.tx_myttaddressmap = {$plugin.tx_myttaddressmap.view.cssFile}


page.includeJS {
  myttaddressmap10.forceOnTop = 1
  myttaddressmap10.if.isTrue = {$plugin.tx_myttaddressmap.view.includejQueryCore}
  myttaddressmap10 = {$plugin.tx_myttaddressmap.view.jQueryFile}
  myttaddressmap10.insertData = 1
}

page.includeJSFooterlibs.myttaddressmap_js1 = {$plugin.tx_myttaddressmap.view.javascriptFile}

plugin.tx_myttaddressmap.features.requireCHashArgumentForActionArguments = 0

