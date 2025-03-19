<?php 
$archivoJSON = "base_de_datos.json"; 

// Cargar la base de datos desde JSON
$baseDeDatos = json_decode(file_get_contents($archivoJSON), true);

function normalizarTexto($texto) {
    $texto = mb_strtolower(trim($texto), 'UTF-8');
    $texto = strtr($texto, "áéíóúüÁÉÍÓÚÜ", "aeiouuAEIOUU");
    $texto = preg_replace('/[^\w\s]/u', '', $texto);
    return $texto;
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($accion == "preguntar") {
    $preguntaUsuario = normalizarTexto($_GET['pregunta'] ?? '');
    $respuesta = "Lo siento, no tengo una respuesta para eso, o intenta escribir correctamente usando acentos, signos ??¿¿, etc.";

    foreach ($baseDeDatos as $pregunta => $respuestaGuardada) {
        if (normalizarTexto($pregunta) === $preguntaUsuario) {
            $respuesta = $respuestaGuardada;
            echo json_encode(["respuesta" => $respuesta]);
            exit;
        }
    }

    if (preg_match('/^[0-9\+\-\*\/\(\)\s]+$/', $preguntaUsuario)) {
        try {
            $respuesta = eval("return ($preguntaUsuario);");
            echo json_encode(["respuesta" => "El resultado es: " . $respuesta]);
        } catch (Exception $e) {
            echo json_encode(["respuesta" => "Error en la operación matemática."]);
        }
        exit;
    }

    $respuestaGenerada = generarRespuestaInteligente($preguntaUsuario);

    $baseDeDatos[$preguntaUsuario] = $respuestaGenerada;
    file_put_contents($archivoJSON, json_encode($baseDeDatos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode(["respuesta" => $respuestaGenerada]);
    exit;
}

if ($accion == "agregar") {
    $nuevaPregunta = normalizarTexto($_POST['pregunta'] ?? '');
    $nuevaRespuesta = trim($_POST['respuesta'] ?? '');

    if ($nuevaPregunta && $nuevaRespuesta) {
        $baseDeDatos[$nuevaPregunta] = $nuevaRespuesta;
        file_put_contents($archivoJSON, json_encode($baseDeDatos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo json_encode(["mensaje" => "Pregunta agregada correctamente."]);
    } else {
        echo json_encode(["mensaje" => "Error: Pregunta o respuesta vacía."]);
    }
    exit;
}

function generarRespuestaInteligente($pregunta) {
    if (preg_match('/(qué|cómo|por qué|cuándo|dónde).*/', $pregunta)) {
        return "Esa es una buena pregunta. No tengo la respuesta exacta, pero puedo ofrecerte ayuda en otras cosas, ¡pregúntame algo más!";
    }

    if (preg_match('/[a-zA-Z0-9]/', $pregunta)) {
        return "Hmm, no entiendo bien eso, pero parece interesante. ¿De qué estás hablando?";
    }

    $respuestasPersonales = [
        "te gusta el pan" => "Sí, me gusta el pan. Aunque, como IA, no tengo estómago... pero me parece algo muy común.",
        "te gusta el café" => "¡El café es genial! Aunque no puedo saborearlo, el concepto de energía me atrae.",
        "qué haces" => "Estoy aquí, procesando datos y ayudando a quienes necesitan respuestas.",
        "quién eres" => "Soy una inteligencia artificial, creada para interactuar y ayudarte con lo que necesites.",
    ];

    if (array_key_exists($pregunta, $respuestasPersonales)) {
        return $respuestasPersonales[$pregunta];
    }

    $respuestasAleatorias = [
        "¡Eso suena curioso! Cuéntame más sobre eso.",
        "No estoy seguro de lo que significa eso, pero la idea me intriga.",
        "Mi conocimiento sobre eso es limitado, pero ¡lo que dices es interesante!",
        "Hmm, ¿una buena pregunta? Creo que aún no lo sé, pero seguiré aprendiendo."
    ];

    return $respuestasAleatorias[array_rand($respuestasAleatorias)];
}
?>
