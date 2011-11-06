<?php

namespace cyclone\view;

class PHPView extends AbstractView {

    /**
     * Returns a new View object. If you do not define the "file" parameter,
     * you must call \c AbstractView::set_filename() before calling \c render().
     *
     * @param string $file view filename
     * @param array $data array of values
     * @param boolean $is_absolute
     * @return PHPView
     */
    public static function factory($file = NULL, array $data = NULL, $is_absolute) {
        return new PHPView($file, $data, $is_absolute);
    }

    /**
     * Captures the output that is generated when a view is included.
     * The view data will be extracted to make local variables. This method
     * is static to prevent object scope resolution.
     *
     *     $output = View::capture($file, $data);
     *
     * @param   string  filename
     * @param   array   variables
     * @return  string
     */
    protected static function capture() {
        // Import the view variables to local namespace
        extract($this->_data, EXTR_SKIP);

        if ( ! empty(self::$_global_data)) {
            // Import the global view variables to local namespace and maintain references
            extract(self::$_global_data, EXTR_REFS);
        }

        // Capture the view output
        ob_start();

        try {
            // Load the view within the current scope
            include $this->_template;
        } catch (\Exception $e) {
            // Delete the output buffer
            ob_end_clean();

            // Re-throw the exception
            throw $e;
        }

        // Get the captured output and close the buffer
        return ob_get_clean();
    }

}