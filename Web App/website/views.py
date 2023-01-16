from flask import Blueprint, render_template

#establish route for website
views = Blueprint('views', __name__)

#define homepage route
@views.route('/')
def  home():
    return render_template("home.html")
