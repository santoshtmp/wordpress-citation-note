/**
 * https://developer.wordpress.org/block-editor/how-to-guides/format-api/
 */
if (typeof yiplcifoAjax === "undefined" || yiplcifoAjax.allow_citation) {
  (function (wp) {
    const { registerFormatType, toggleFormat } = wp.richText;
    const { BlockControls } = wp.blockEditor || wp.editor;
    // const { RichTextToolbarButton } = wp.blockEditor || wp.editor;
    const { ToolbarGroup, ToolbarButton } = wp.components;
    const { createElement, Fragment } = wp.element;

    const YiplCitationButton = ({ isActive, onChange, value }) => {
      // return createElement(
      //     RichTextToolbarButton,
      //     {
      //         icon: 'editor-ul',
      //         title: 'YIPL Citation',
      //         onClick: () => {
      //             onChange(toggleFormat(value, { type: 'yipl/citation', })
      //             );
      //         },
      //         isActive: isActive,
      //     }
      // );
      return createElement(
        Fragment,
        null,
        createElement(
          BlockControls,
          null,
          createElement(
            ToolbarGroup,
            null,
            createElement(ToolbarButton, {
              icon: "format-quote", //'editor-ol' 'format-quote'
              label: "Citation",
              title: "YIPL Citation",
              onClick: () => {
                onChange(
                  toggleFormat(value, {
                    type: "yipl/citation",
                  })
                );
              },
              isActive: isActive,
            })
          )
        )
      );
    };

    registerFormatType("yipl/citation", {
      title: "YIPL Citation",
      tagName: "yipl_citation_placeholder",
      className: null,
      edit: YiplCitationButton,
    });
  })(window.wp);
}

/**
 *
 */
jQuery(document).ready(function ($) {
  // Initialize TinyMCE for existing textareas
  // add new fields when clicking the add button
  $("#yipl-citation-add-repeater-group").on("click", function (e) {
    e.preventDefault();
    const $button = $(this);
    $button.prop("disabled", true); // Prevent rapid multiple clicks

    let ajax = $.ajax({
      url: yiplcifoAjax.ajax_url,
      type: "POST",
      data: {
        action: yiplcifoAjax.action_yipl_citation_fields,
        _nonce: yiplcifoAjax.nonce,
        row_number: $("#yipl-citation-repeater-table tbody tr").length + 1,
      },
    });

    ajax.done(function (response) {
      let row = $(response);
      $("#yipl-citation-repeater-table tbody").append(row);

      // Reinitialize TinyMCE if the row contains an editor
      row.find("textarea").each(function () {
        const editorId = $(this).attr("id");
        if (editorId && typeof tinymce !== "undefined") {
          tinymce.execCommand("mceAddEditor", false, editorId);
        }
      });
    });
    ajax.fail(function (response) {
      console.error("Error:", response.responseText);
    });
    ajax.always(function (response) {
      // console.log(response);
      $button.prop("disabled", false);
    });
  });

  // Initialize TinyMCE for existing textareas
  // This will initialize TinyMCE for all textareas in the table
  $("#yipl-citation-repeater-table tbody tr").each(function () {
    $(this)
      .find("textarea")
      .each(function () {
        const editorId = $(this).attr("id");
        if (editorId && typeof tinymce !== "undefined") {
          tinymce.execCommand("mceAddEditor", false, editorId);
        }
      });
  });

  // Remove group button
  // This will remove the group from the table
  $(document).on("click", ".yipl-citation-remove-group", function () {
    $(this).prop("disabled", true);
    if (confirm("Are you sure you want to remove this citation?")) {
      $(this).closest("tr").remove();
    }
    $(this).prop("disabled", false);
  });

  /**
   * Validate number input to allow only digits
   * This will allow only digits in the input field
   * and remove any non-digit characters
   */
  $(document).on("input", ".input-row_number", function () {
    let value = $(this).val().replace(/\D/g, "");
    $(this).val(value);
  });

  // Validate number input for duplicates
  // generate a unique placeholder for each input
  $(document).on(
    "blur",
    "#yipl-citation-repeater-table .input-row_number",
    function () {
      var $input = $(this);
      var index = $input.data("index");
      var value = $input.val();

      // Check for duplicates
      var isDuplicate = false;
      $(".input-row_number")
        .not($input)
        .each(function () {
          if ($(this).val().trim() === value) {
            isDuplicate = true;
            return false; // break loop
          }
        });
      // If the value is empty, we don't consider it a duplicate
      if (isDuplicate) {
        $input.val("");
        $input.css("border", "2px solid red");
        $(".yi_citation_" + index).text("Duplicate!   " + "citation_" + value);
      } else {
        $input.css("border", "");
        $(".yi_citation_" + index).text("citation_" + value);
      }
      //
    }
  );

  // Toggle collapse/expand
  $("#yipl-citation-repeater-table").on(
    "click",
    ".toggle-yi-citation-row",
    function () {
      const $tr = $(this).closest("tr");
      // Toggle the expand/collapse of the row
      $tr.find(".citation-expandable").toggle();
      $tr.find(".citation-collapseable").toggle();

      // Optionally, change the button text when toggled (e.g., "Show" / "Hide")
      const buttonText = $tr.find(".citation-expandable").is(":visible")
        ? "Collapse"
        : "Expand";
      $(this).text(buttonText);
    }
  );

  // Collapse all rows
  $("#yipl-collapse-all").click(function () {
    $("#yipl-citation-repeater-table tbody tr .citation-expandable").each(
      function () {
        $(this).hide(); // Hide all expandable content
      }
    );
    $("#yipl-citation-repeater-table tbody tr .citation-collapseable").each(
      function () {
        $(this).show(); // Hide all expandable content
      }
    );
    $("#yipl-citation-repeater-table tbody tr .toggle-yi-citation-row").each(
      function () {
        $(this).text("Expand");
      }
    );
  });

  // Expand all rows
  $("#yipl-expand-all").click(function () {
    $("#yipl-citation-repeater-table tbody tr .citation-expandable").each(
      function () {
        $(this).show(); // Show all expandable content
      }
    );
    $("#yipl-citation-repeater-table tbody tr .citation-collapseable").each(
      function () {
        $(this).hide(); // Hide all expandable content
      }
    );
    $("#yipl-citation-repeater-table tbody tr .toggle-yi-citation-row").each(
      function () {
        $(this).text("Collapse");
      }
    );
  });

  // Re-arrange rows
  $("#yipl-citation-repeater-table tbody").sortable({
    handle: ".row-drag-handler",
    axis: "y",
    // update: function (event, ui) {
    // },
    stop: function (event, ui) {
      try {
        // Try reinitializing TinyMCE for any textarea in the moved row
        ui.item.find("textarea").each(function () {
          let editorId = $(this).attr("id");
          // if (editorId && typeof tinymce !== 'undefined') {
          // Remove existing editor (if any)
          tinymce.execCommand("mceRemoveEditor", false, editorId);
          // Re-add TinyMCE
          tinymce.execCommand("mceAddEditor", false, editorId);
          // }
        });
        // Remove inline width styles added during sort
        ui.item.find("td").css("width", "");
      } catch (error) {
        // Revert sort by canceling the move
        $tbody.sortable("cancel");
      }
    },
  });
});
