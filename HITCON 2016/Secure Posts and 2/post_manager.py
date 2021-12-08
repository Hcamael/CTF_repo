from flask import Flask
import config

# init app
app = Flask(__name__)
app.secret_key = config.flag1
accept_datatype = ['json', 'yaml']

from flask import Response
from flask import request, session
from flask import redirect, url_for, safe_join, abort
from flask import render_template_string

# load utils
def load_eval(data):
    return eval(data)

def load_pickle(data):
    import pickle
    return pickle.loads(data)

def load_json(data):
    import json
    return json.loads(data)

def load_yaml(data):
    import yaml
    return yaml.load(data)

# dump utils
def dump_eval(data):
    return repr(data)

def dump_pickle(data):
    import pickle
    return pickle.dumps(data)

def dump_json(data):
    import json
    return json.dumps(data)

def dump_yaml(data):
    import yaml
    return yaml.dump(data)


def render_template(filename, **args):
    with open(safe_join(app.template_folder, filename)) as f:
        template = f.read()
    name = session.get('name', 'anonymous')[:10]
    return render_template_string(template.format(name=name), **args)

def load_posts():
    handlers = {
        # disabled insecure data type
        #"eval": load_eval,
        #"pickle": load_pickle,

        "json": load_json,
        "yaml": load_yaml
    }

    datatype = session.get("post_type", config.default_datatype)
    data = session.get("post_data", config.default_data)

    if datatype not in handlers: abort(403)
    return handlers[datatype](data)

def store_posts(posts, datatype):
    handlers = {
        "eval": dump_eval,
        "pickle": dump_pickle,

        "json": dump_json,
        "yaml": dump_yaml
    }
    if datatype not in handlers: abort(403)
    data = handlers[datatype](posts)

    session["post_type"] = datatype
    session["post_data"] = data


@app.route('/')
def index():
    posts = load_posts()
    return render_template('index.html', posts = posts, accept_datatype = accept_datatype)

@app.route('/post', methods=['POST'])
def add_post():
    posts = load_posts()

    title = request.form.get('title', 'empty')
    content = request.form.get('content', 'empty')
    datatype = request.form.get('datatype', 'json')
    if datatype not in accept_datatype: abort(403)
    name = request.form.get('author', 'anonymous')[:10]

    from datetime import datetime
    posts.append({
        'title': title,
        'author': name,
        'content': content,
        'date': datetime.now().strftime("%B %d, %Y %X")
    })
    session["name"] = name
    store_posts(posts, datatype)
    return redirect(url_for('index'))

@app.route('/source')
def get_source():
    with open(__file__, "r") as f:
        resp = f.read()
    return Response(resp, mimetype="text/plain")
