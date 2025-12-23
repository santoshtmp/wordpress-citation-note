/**
 * https://developer.wordpress.org/block-editor/how-to-guides/format-api/
 */
if (typeof citenoteAjax === "undefined" || citenoteAjax.allow_citation) {
  (function (wp) {
    const { registerFormatType, toggleFormat } = wp.richText;
    const { BlockControls } = wp.blockEditor || wp.editor;
    // const { RichTextToolbarButton } = wp.blockEditor || wp.editor;
    const { ToolbarGroup, ToolbarButton } = wp.components;
    const { createElement, Fragment } = wp.element;

    const CitationNoteButton = ({ isActive, onChange, value }) => {
      // return createElement(
      //     RichTextToolbarButton,
      //     {
      //         icon: 'editor-ul',
      //         title: 'citenote Citation',
      //         onClick: () => {
      //             onChange(toggleFormat(value, { type: 'citenote/citation', })
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
              label: "Citation Note",
              title: "Citation Note",
              onClick: () => {
                onChange(
                  toggleFormat(value, {
                    type: "citenote/placeholder",
                  })
                );
              },
              isActive: isActive,
            })
          )
        )
      );
    };

    registerFormatType("citenote/placeholder", {
      title: "Citation Note",
      tagName: "citenoteplaceholder",
      className: null,
      edit: CitationNoteButton,
    });
  })(window.wp);
}

/**
 *
 */
jQuery(function ($) {
  // jQuery(document).ready(function ($) {  });
  $(window).on("load", function () {

    // 
    function initializeEditor($textarea) {
      const editorId = $textarea.attr("id");
      if (!editorId || !window.tinymce) return;
      // Avoid double initialization
      if (!tinymce.get(editorId) && editorId && typeof tinymce !== "undefined") {
        tinymce.execCommand("mceAddEditor", false, editorId);
      }
    }

    // 
    function destroyEditor($textarea) {
      const editorId = $textarea.attr("id");
      if (editorId && tinymce.get(editorId)) {
        // tinymce.get(editorId).remove();
        tinymce.execCommand("mceRemoveEditor", false, editorId);
      }
    }

    // Initialize TinyMCE for existing textareas
    // add new fields when clicking the add button
    $("#citation-note-add-repeater-group").on("click", function (e) {
      e.preventDefault();
      const $button = $(this);
      $button.prop("disabled", true); // Prevent rapid multiple clicks

      let ajax = $.ajax({
        url: citenoteAjax.ajax_url,
        type: "POST",
        data: {
          action: citenoteAjax.action_name,
          _nonce: citenoteAjax.nonce,
          row_number: $("#citation-note-repeater-table tbody tr").length + 1,
        },
      });

      ajax.done(function (response) {
        let row = $(response);
        $("#citation-note-repeater-table tbody").append(row);

        // Reinitialize TinyMCE if the row contains an editor
        row.find("textarea").each(function () {
          initializeEditor($(this));
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
    // $("#citation-note-repeater-table tbody tr").each(function () {
    //   $(this)
    //     .find("textarea")
    //     .each(function () {
    //       initializeEditor($(this));
    //     });
    // });

    // Remove group button
    // This will remove the group from the table
    $(document).on("click", ".citation-note-remove-group", function () {
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
      "#citation-note-repeater-table .input-row_number",
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
    $("#citation-note-repeater-table").on(
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
    $("#citenote-collapse-all").click(function () {
      $("#citation-note-repeater-table tbody tr .citation-expandable").each(
        function () {
          $(this).hide(); // Hide all expandable content
        }
      );
      $("#citation-note-repeater-table tbody tr .citation-collapseable").each(
        function () {
          $(this).show(); // Hide all expandable content
        }
      );
      $("#citation-note-repeater-table tbody tr .toggle-yi-citation-row").each(
        function () {
          $(this).text("Expand");
        }
      );
    });

    // Expand all rows
    $("#citenote-expand-all").click(function () {
      $("#citation-note-repeater-table tbody tr .citation-expandable").each(
        function () {
          $(this).show(); // Show all expandable content
        }
      );
      $("#citation-note-repeater-table tbody tr .citation-collapseable").each(
        function () {
          $(this).hide(); // Hide all expandable content
        }
      );
      $("#citation-note-repeater-table tbody tr .toggle-yi-citation-row").each(
        function () {
          $(this).text("Collapse");
        }
      );
    });

    // Re-arrange rows
    $("#citation-note-repeater-table tbody").sortable({
      handle: ".row-drag-handler",
      axis: "y",
      // update: function (event, ui) {
      // },
      stop: function (event, ui) {
        try {
          // Try reinitializing TinyMCE for any textarea in the moved row
          ui.item.find("textarea").each(function () {
            destroyEditor($(this));
            initializeEditor($(this));
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
});
