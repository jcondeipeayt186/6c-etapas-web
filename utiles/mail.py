from email.message import EmailMessage
import smtplib

#remitente puede ser una cuenta tuya (este ejemplo funciono con una cuenta de gmail).
# apppasswords: esta clave de 16 digitos debes generarla. Ver como hacerlo al final de este archivo
def enviarMail(remitente, destinatario, mensaje, apppasswords):
  email = EmailMessage()
  email["From"] = remitente
  email["To"] = destinatario
  email["Subject"] = "Correo de prueba"#El Asunto
  email.set_content(mensaje) # El mensaje
  smtp = smtplib.SMTP_SSL("smtp.gmail.com")
  smtp.login(remitente, apppasswords)
  smtp.sendmail(remitente, destinatario, email.as_string())
  smtp.quit()


#EJEMPLO DE USO
rte = "julianconde.ispc@gmail.com"#aca pone tu cuenta de gmail
dest = "julianconde.ispc@gmail.com"#aca lka cuenta de destino
msje = "¡Mail enviado desde Python!"
apppswds = "irglvrmkjkfgxpfp" #esta clave de 16 digitos debes generarla. Ver como hacerlo al final de este archivo

enviarMail(rte,dest,msje,apppswds)


"""
Info a Octubre 2023 (dejo fecha porque suelen ir cambiando las formas en google)

Las contraseñas de la aplicación te permiten acceder a tu Cuenta de Google en apps y servicios más
 antiguos que no son compatibles con los estándares de seguridad modernos.
Las contraseñas de la aplicación son menos seguras que usar apps y servicios actualizados que cuentan
 con estándares de seguridad modernos. Antes de crear una contraseña de la aplicación, debes verificar
   si la app la necesita para acceder.
Puedes generar una contraseña de aplicación visitando esta página (https://myaccount.google.com/apppasswords) 
mientras accedes a tu cuenta de Google.

Video explicativo: https://www.youtube.com/watch?v=oPAo8Hh8bj0
"""
