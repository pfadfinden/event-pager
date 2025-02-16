FROM python:3-alpine

ENV PYTHONUNBUFFERED 1
RUN mkdir /code
WORKDIR /code

# No requirements for this script
# COPY requirements.txt /code/
# RUN pip install -r requirements.txt

COPY server.py /code/
ENTRYPOINT [ "python", "-u", "/code/server.py" ]