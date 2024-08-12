import DrupalHtmlBuilder from '../../../../js/ckeditor5_plugins/drupalHtmlEngine/src/drupalhtmlbuilder';

describe('drupalHtmlBuilderTest', () => {
  it('should return empty string when empty DocumentFragment is passed', () => {
    const drupalHtmlBuilder = new DrupalHtmlBuilder();
    drupalHtmlBuilder.appendNode(document.createDocumentFragment());
    expect(drupalHtmlBuilder.build()).toBe('');
  });

  it('should create text from single text node', () => {
    const drupalHtmlBuilder = new DrupalHtmlBuilder();
    const text = 'foo bar';
    const fragment = document.createDocumentFragment();
    const textNode = document.createTextNode(text);
    fragment.appendChild(textNode);

    drupalHtmlBuilder.appendNode(fragment);
    expect(drupalHtmlBuilder.build()).toBe(text);
  });

  it('should return correct HTML from fragment with paragraph', () => {
    const drupalHtmlBuilder = new DrupalHtmlBuilder();
    const fragment = document.createDocumentFragment();
    const paragraph = document.createElement('p');
    paragraph.textContent = 'foo bar';
    fragment.appendChild(paragraph);

    drupalHtmlBuilder.appendNode(fragment);
    expect(drupalHtmlBuilder.build()).toBe('<p>foo bar</p>');
  });

  it('should return correct HTML from fragment with multiple child nodes', () => {
    const drupalHtmlBuilder = new DrupalHtmlBuilder();
    const fragment = document.createDocumentFragment();
    const text = document.createTextNode('foo bar');
    const paragraph = document.createElement('p');
    const div = document.createElement('div');

    paragraph.textContent = 'foo';
    div.textContent = 'bar';

    fragment.appendChild(text);
    fragment.appendChild(paragraph);
    fragment.appendChild(div);

    drupalHtmlBuilder.appendNode(fragment);

    expect(drupalHtmlBuilder.build()).toBe('foo bar<p>foo</p><div>bar</div>');
  });

  it('should return correct HTML scripts and styles', () => {
    const drupalHtmlBuilder = new DrupalHtmlBuilder();
    const fragment = document.createDocumentFragment();
    const script = document.createElement('script');
    script.textContent = `let x = 10;
let y = 5;
if (y < x) {
console.log('is smaller')
}`;
    const style = document.createElement('style');
    style.setAttribute('type', 'text/css');
    style.appendChild(
      document.createTextNode(':root .sections > h2 { background: red}'),
    );

    fragment.appendChild(style);
    fragment.appendChild(document.createTextNode('\n'));
    fragment.appendChild(script);

    drupalHtmlBuilder.appendNode(fragment);

    expect(drupalHtmlBuilder.build())
      .toBe(`<style type="text/css">:root .sections > h2 { background: red}</style>
<script>let x = 10;
let y = 5;
if (y < x) {
console.log('is smaller')
}</script>`);
  });

  it('should return correct HTML from fragment with comment', () => {
    const drupalHtmlBuilder = new DrupalHtmlBuilder();
    const fragment = document.createDocumentFragment();
    const div = document.createElement('div');
    const comment = document.createComment('bar');
    div.textContent = 'bar';

    fragment.appendChild(div);
    fragment.appendChild(comment);

    drupalHtmlBuilder.appendNode(fragment);

    expect(drupalHtmlBuilder.build()).toBe('<div>bar</div><!--bar-->');
  });

  it('should return correct HTML from fragment with attributes', () => {
    const drupalHtmlBuilder = new DrupalHtmlBuilder();
    const fragment = document.createDocumentFragment();
    const div = document.createElement('div');
    div.setAttribute('id', 'foo');
    div.classList.add('bar');
    div.textContent = 'baz';

    fragment.appendChild(div);
    drupalHtmlBuilder.appendNode(fragment);

    expect(drupalHtmlBuilder.build()).toBe(
      '<div id="foo" class="bar">baz</div>',
    );
  });

  it('should return correct HTML from fragment with self closing tag', () => {
    const drupalHtmlBuilder = new DrupalHtmlBuilder();
    const fragment = document.createDocumentFragment();
    const hr = document.createElement('hr');

    fragment.appendChild(hr);
    drupalHtmlBuilder.appendNode(fragment);

    expect(drupalHtmlBuilder.build()).toBe('<hr>');
  });

  it('attribute values should be escaped', () => {
    const drupalHtmlBuilder = new DrupalHtmlBuilder();
    const fragment = document.createDocumentFragment();
    const div = document.createElement('div');
    div.setAttribute('data-caption', 'Kittens & llamas are <em>cute</em>');
    div.textContent = 'foo';

    fragment.appendChild(div);
    drupalHtmlBuilder.appendNode(fragment);

    expect(drupalHtmlBuilder.build()).toBe(
      '<div data-caption="Kittens &amp; llamas are &lt;em&gt;cute&lt;/em&gt;">foo</div>',
    );
  });
});
