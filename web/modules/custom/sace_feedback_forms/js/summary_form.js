console.log('hi')
document.addEventListener('DOMContentLoaded', () => {
console.log('hi')
  // <div class='form-item sace-feedback-forms-option-summary-total'>
  const optionSummaryTotals = Array.from(document.getElementsByClassName('sace-feedback-forms-option-summary-total'));

  optionSummaryTotals.forEach((totalContainer) => {

    const inputs = Array.from(totalContainer.closest('.form-wrapper').querySelectorAll('input'));

    const updateTotal = () => {
      const newTotal = inputs.map((input) => parseInt(input.value)).reduce((a, b) => a + b);
      totalContainer.querySelector('.sace-feedback-forms-option-summary-total__value').innerText = newTotal;
    }

    updateTotal();

    inputs.forEach((input) => input.addEventListener('change', updateTotal));
  });
})