from flask import Blueprint

#establish route for website
views = Blueprint('views', __name__)

#define homepage route
@views.route('/')
def  home():
    return "<h1>Test</h1>"