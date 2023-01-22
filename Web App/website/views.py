from flask import Blueprint, render_template
from flask_login import login_required, current_user

#establish route for website
views = Blueprint('views', __name__)

#define homepage route
@views.route('/')
@login_required
def  home():
    return render_template("home.html")
