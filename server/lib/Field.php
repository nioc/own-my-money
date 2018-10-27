<?php
/**
 * Form field definition for step processing.
 *
 * @version 1.0.0
 *
 * @internal
 */
class Field
{
    /**
     * @var string Field name
     */
    public $name;

    /**
     * @var string Field label
     */
    public $label;

    /**
     * @var string Field type (text, password, ...)
     */
    public $type;

    /**
     * @var string Field validation rules
     */
    public $validate;

    /**
     * @var string Field placeholder
     */
    public $placeholder;

    /**
     * @var string Field help text
     */
    public $help;

    /**
     * @var string Field value
     */
    public $value;

    /**
     * Initializes a Field object with the provided informations.
     *
     * @param string $name         Name used for retrieving field
     * @param string $label        Label displayed in form
     * @param string $type         Input type displayed in form (text, password, ...)
     * @param string $validate     Validation rules (see https://baianat.github.io/vee-validate/guide/rules.html)
     * @param string $help         Helper text to explain what field is used for
     * @param string $placeholder  Placeholder displayed in field
     */
    public function __construct($name = null, $label = null, $type = null, $validate = null, $help = null, $placeholder = null)
    {
        if ($name !== null) {
            $this->name = $name;
        }
        if ($label !== null) {
            $this->label = $label;
        }
        if ($type !== null) {
            $this->type = $type;
        }
        if ($validate !== null) {
            $this->validate = $validate;
        }
        if ($help !== null) {
            $this->help = $help;
        }
        if ($placeholder !== null) {
            $this->placeholder = $placeholder;
        }
    }
}
