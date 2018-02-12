const test = require('tape')

const cliPath = '../lib/cli'

test('parses multiple files', function (t) {
  t.plan(3)

  const cli = require(cliPath)
  const argv = ['example/file1.mermaid', 'file2.mermaid', 'file3.mermaid']
  const expect = ['example/file1.mermaid', 'file2.mermaid', 'file3.mermaid']

  cli.parse(argv, function (err, msg, opt) {
    t.ok(!err, 'no err')
    t.equal(opt.files.length, 3, 'should have 3 parameters')
    t.deepEqual(opt.files, expect, 'should match expected values')

    t.end()
  })
})

test('defaults to png', function (t) {
  t.plan(3)

  const cli = require(cliPath)
  const argv = ['example/file1.mermaid']

  cli.parse(argv, function (err, msg, opt) {
    t.ok(!err, 'no err')
    t.ok(opt.png, 'png is set by default')
    t.notOk(opt.svg, 'svg is not set by default')

    t.end()
  })
})

test('setting svg unsets png', function (t) {
  t.plan(3)

  const cli = require(cliPath)
  const argv = ['example/file1.mermaid', '-s']

  cli.parse(argv, function (err, msg, opt) {
    t.ok(!err, 'no err')
    t.ok(opt.svg, 'svg is set when requested')
    t.notOk(opt.png, 'png is unset when svg is set')

    t.end()
  })
})

test('setting png and svg is allowed', function (t) {
  t.plan(3)

  const cli = require(cliPath)
  const argv = ['example/file1.mermaid', '-s', '-p']

  cli.parse(argv, function (err, msg, opt) {
    t.ok(!err, 'no err')
    t.ok(opt.png, 'png is set when requested')
    t.ok(opt.svg, 'svg is set when requested')

    t.end()
  })
})

test('setting an output directory succeeds', function (t) {
  t.plan(2)

  const cli = require(cliPath)
  const argv = ['example/file1.mermaid', '-o', 'example/']

  cli.parse(argv, function (err, msg, opt) {
    t.ok(!err, 'no err')
    t.equal(opt.outputDir, 'example/', 'output directory is set')
    t.end()
  })
})

test('not setting a css source file uses a default style', function (t) {
  t.plan(2)

  const cli = require(cliPath)
  const argv = ['example/file1.mermaid']

  cli.parse(argv, function (err, msg, opt) {
    t.ok(!err, 'no err')
    t.ok(opt.css, 'css file is populated')
    t.end()
  })
})

test('setting a css source file succeeds', function (t) {
  t.plan(2)

  const cli = require(cliPath)
  const argv = ['example/file1.mermaid', '-t', 'test/fixtures/test.css']

  cli.parse(argv, function (err, msg, opt) {
    t.ok(!err, 'no err')
    t.ok(opt.css, 'css file is populated')
    t.end()
  })
})

test('setting an output directory incorrectly causes an error', function (t) {
  t.plan(1)

  const cli = require(cliPath)
  const argv = ['-o']

  cli.parse(argv, function (err) {
    t.ok(err, 'an error is raised')

    t.end()
  })
})

test('a callback function is called after parsing', function (t) {
  t.plan(3)

  const cli = require(cliPath)
  const argv = ['example/file1.mermaid']

  cli.parse(argv, function (err, msg, opts) {
    t.ok(!err, 'no err')
    t.ok(true, 'callback was called')
    t.deepEqual(argv, opts.files, 'options are as expected')

    t.end()
  })
})
